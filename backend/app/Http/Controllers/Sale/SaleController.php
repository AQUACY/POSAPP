<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\BaseController;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SaleController extends BaseController
{
    /**
     * Display a listing of the sales.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Sale::where('branch_id', $user->branch_id)
            ->with(['customer', 'items.inventory', 'payments']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->sendResponse($sales, 'Sales retrieved successfully');
    }

    /**
     * Store a newly created sale in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,credit_card,ghana_payment',
            'notes' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $user = Auth::user();
            $sale = Sale::create([
                'sale_number' => 'SALE-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT),
                'total_amount' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'final_amount' => 0,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'status' => 'pending',
                'notes' => $request->notes ?? null,
                'payment_url' => $request->payment_method !== 'cash' ? config('app.url') . '/payment/' . Str::random(32) : null,
                'business_id' => $user->business_id,
                'branch_id' => $user->branch_id,
                'cashier_id' => $user->id,
                'customer_id' => $request->customer_id,
            ]);

            $totalAmount = 0;
            $totalDiscount = 0;

            foreach ($request->items as $item) {
                $inventory = Inventory::where('branch_id', $user->branch_id)
                    ->findOrFail($item['inventory_id']);

                if ($inventory->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for item: {$inventory->name}");
                }

                $unitPrice = round($inventory->unit_price, 2);
                $quantity = $item['quantity'];
                $discount = 0;
                $itemTotal = round(($unitPrice - $discount) * $quantity, 2);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'inventory_id' => $inventory->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discount,
                    'tax_amount' => 0, // Tax will be calculated at the sale level
                    'total_amount' => $itemTotal,
                ]);

                $inventory->decrement('quantity', $quantity);

                $totalAmount += $itemTotal;
                $totalDiscount += $discount;
            }

            // Update sale with initial totals
            $sale->update([
                'total_amount' => $totalAmount,
                'discount_amount' => $totalDiscount,
            ]);

            // Calculate taxes using the Sale model's method
            $sale->calculateTaxes();

            // Calculate final amount including taxes
            $finalAmount = round($totalAmount - $totalDiscount + $sale->tax_amount, 2);

            // Update sale with final amounts
            $sale->update([
                'final_amount' => $finalAmount,
            ]);

            // Create initial payment record
            Payment::create([
                'sale_id' => $sale->id,
                'amount' => $finalAmount,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'reference' => Str::random(10),
            ]);

            DB::commit();

            // Load the sale with its relationships and return it
            $sale = $sale->fresh(['items.inventory', 'customer', 'payments']);
            
            return $this->sendResponse($sale, 'Sale created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error creating sale', $e->getMessage(), 500);
        }
    }

    /**
     * Process payment for a pending sale
     *
     * @param Request $request
     * @param int $businessId
     * @param int $branchId
     * @param int $id
     * @return JsonResponse
     */
    public function processPayment(Request $request, int $businessId, int $branchId, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:' . implode(',', [
                Sale::PAYMENT_METHOD_CASH,
                Sale::PAYMENT_METHOD_CREDIT_CARD,
                Sale::PAYMENT_METHOD_GHANA_PAYMENT
            ]),
            'reference' => 'required_if:payment_method,credit_card,ghana_payment|string',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            // First check if sale exists and belongs to the correct business/branch
            $sale = Sale::where('id', $id)
                ->where('business_id', $businessId)
                ->where('branch_id', $branchId)
                ->first();

            if (!$sale) {
                throw new \Exception('Sale not found');
            }

            \Log::info('Processing payment for sale', [
                'sale_id' => $sale->id,
                'business_id' => $businessId,
                'branch_id' => $branchId,
                'current_status' => $sale->status,
                'current_payment_status' => $sale->payment_status,
                'is_pending' => $sale->isPending(),
                'payment_method' => $request->payment_method,
                'amount' => $request->amount
            ]);

            // Then check if it's in pending status
            if (!$sale->isPending()) {
                throw new \Exception('Sale is not in pending status. Current status: ' . $sale->status);
            }

            // Round both amounts to 2 decimal places for comparison
            $saleAmount = round($sale->final_amount, 2);
            $paymentAmount = round($request->amount, 2);

            if ($saleAmount != $paymentAmount) {
                throw new \Exception('Payment amount does not match sale amount');
            }

            // Find or create payment record
            $payment = Payment::where('sale_id', $sale->id)
                ->where('status', Payment::STATUS_PENDING)
                ->first();

            if (!$payment) {
                $payment = Payment::create([
                    'sale_id' => $sale->id,
                    'amount' => $paymentAmount,
                    'payment_method' => $request->payment_method,
                    'status' => Payment::STATUS_PENDING,
                    'reference' => $request->reference ?? Str::random(10),
                ]);
            }

            // Update payment status
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'reference' => $request->reference ?? $payment->reference,
                'completed_at' => now(),
            ]);

            // Update sale status
            $sale->update([
                'status' => Sale::STATUS_COMPLETED,
                'payment_status' => Sale::PAYMENT_STATUS_COMPLETED,
            ]);

            \Log::info('Payment processed successfully', [
                'sale_id' => $sale->id,
                'new_status' => $sale->status,
                'new_payment_status' => $sale->payment_status,
                'payment_id' => $payment->id
            ]);

            DB::commit();

            return $this->sendResponse($sale->load('items.inventory', 'customer', 'payments'), 'Payment processed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error processing payment', [
                'sale_id' => $id,
                'business_id' => $businessId,
                'branch_id' => $branchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Error processing payment', $e->getMessage(), 500);
        }
    }

    /**
     * Get pending sales that need payment processing
     *
     * @return JsonResponse
     */
    public function getPendingSales(): JsonResponse
    {
        $user = Auth::user();
        $pendingSales = Sale::where('branch_id', $user->branch_id)
            ->where('status', 'pending')
            ->with(['customer', 'items.inventory', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->sendResponse($pendingSales, 'Pending sales retrieved successfully');
    }

    /**
     * Cancel a pending sale
     *
     * @param int $id
     * @return JsonResponse
     */
    public function cancelSale(int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $sale = Sale::where('status', 'pending')
                ->where('id', $id)
                ->firstOrFail();

            // Restore inventory quantities
            foreach ($sale->items as $item) {
                $inventory = Inventory::find($item->inventory_id);
                $inventory->increment('quantity', $item->quantity);
            }

            // Update sale status
            $sale->update([
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
            ]);

            // Update payment status
            $sale->payments()->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            return $this->sendResponse($sale->load('items.inventory', 'customer', 'payments'), 'Sale cancelled successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Error cancelling sale', $e->getMessage(), 500);
        }
    }
} 