<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CashierController extends Controller
{

    /**
     * Create a new sale
     */
    public function createSale(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,credit_card,debit_card,mobile_payment',
            'notes' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // Create sale
            $sale = Sale::create([
                'sale_number' => 'SALE-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT),
                'total_amount' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'final_amount' => 0,
                'payment_method' => $request->payment_method,
                'payment_status' => Sale::PAYMENT_STATUS_PENDING,
                'status' => Sale::STATUS_PENDING,
                'notes' => $request->notes,
                'business_id' => $user->business_id,
                'branch_id' => $user->branch_id,
                'cashier_id' => $user->id,
                'customer_id' => $request->customer_id,
            ]);

            Log::info('Sale created', [
                'sale_id' => $sale->id,
                'status' => $sale->status,
                'payment_status' => $sale->payment_status,
                'payment_method' => $sale->payment_method
            ]);

            $totalAmount = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            // Process each item
            foreach ($request->items as $item) {
                $inventory = Inventory::where('branch_id', $user->branch_id)
                    ->findOrFail($item['inventory_id']);

                // Check stock availability
                if ($inventory->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for item: {$inventory->name}");
                }

                // Calculate item totals
                $unitPrice = $inventory->unit_price;
                $quantity = $item['quantity'];
                $discount = 0; // Could be calculated based on promotions
                $tax = ($unitPrice - $discount) * 0.1; // 10% tax rate
                $itemTotal = ($unitPrice - $discount + $tax) * $quantity;

                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'inventory_id' => $inventory->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discount,
                    'tax_amount' => $tax,
                    'total_amount' => $itemTotal,
                    'sync_status' => 'pending'
                ]);

                // Update inventory
                $inventory->decrement('quantity', $quantity);

                // Update sale totals
                $totalAmount += $itemTotal;
                $totalDiscount += $discount;
                $totalTax += $tax;
            }

            // Calculate taxes using the Sale model's method
            $sale->calculateTaxes();

            // Update sale totals
            $sale->update([
                'total_amount' => $totalAmount,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'final_amount' => $totalAmount - $totalDiscount + $totalTax,
            ]);

            Log::info('Sale updated with totals', [
                'sale_id' => $sale->id,
                'status' => $sale->status,
                'payment_status' => $sale->payment_status,
                'total_amount' => $sale->total_amount,
                'final_amount' => $sale->final_amount
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Sale created successfully',
                'data' => $sale->load('items.inventory', 'customer', 'cashier')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sale', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get sales history
     */
    public function getSales(Request $request)
    {
        $user = Auth::user();
        $query = Sale::where('branch_id', $user->branch_id)
            ->where('cashier_id', $user->id)
            ->with(['customer', 'items.inventory']);

        // Apply date filters if provided
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $sales
        ]);
    }

    /**
     * Get sale details
     */
    public function getSale($id)
    {
        $user = Auth::user();
        $sale = Sale::where('branch_id', $user->branch_id)
            ->where('cashier_id', $user->id)
            ->with(['customer', 'items.inventory', 'cashier'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $sale
        ]);
    }

    /**
     * Get available inventory items
     */
    public function getInventory(Request $request)
    {
        $user = Auth::user();
        $query = Inventory::where('branch_id', $user->branch_id)
            ->where('quantity', '>', 0)
            ->with('category');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $inventory = $query->orderBy('name')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    /**
     * Get customers
     */
    public function getCustomers(Request $request)
    {
        $user = Auth::user();
        $query = Customer::where('business_id', $user->business_id);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $customers
        ]);
    }

    /**
     * Get today's sales summary
     */
    public function getTodaySummary()
    {
        $user = Auth::user();
        $today = now()->startOfDay();

        $summary = Sale::where('branch_id', $user->branch_id)
            ->where('cashier_id', $user->id)
            ->whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(final_amount) as total_amount,
                SUM(discount_amount) as total_discount,
                SUM(tax_amount) as total_tax,
                AVG(final_amount) as average_sale
            ')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    /**
     * Get dashboard data for cashier
     */
    public function dashboard()
    {
        $user = Auth::user();
        $today = now()->startOfDay();

        // Get today's sales summary
        $todaySales = Sale::where('branch_id', $user->branch_id)
            ->whereDate('created_at', $today)
            ->selectRaw('
                COUNT(*) as total_sales,
                SUM(final_amount) as total_amount,
                SUM(discount_amount) as total_discount,
                SUM(tax_amount) as total_tax
            ')
            ->first();

        // Get recent sales
        $recentSales = Sale::where('branch_id', $user->branch_id)
            ->with(['customer', 'items.inventory'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get low stock items
        $lowStockItems = Inventory::where('branch_id', $user->branch_id)
            ->where('quantity', '<', 10)
            ->with('category')
            ->take(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'today_sales' => $todaySales,
                'recent_sales' => $recentSales,
                'low_stock_items' => $lowStockItems
            ]
        ]);
    }

    /**
     * Get sales list with pagination
     */
    public function sales(Request $request)
    {
        $user = Auth::user();
        $query = Sale::where('branch_id', $user->branch_id)
            ->with(['customer', 'items.inventory']);

        // Apply filters if provided
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $sales
        ]);
    }

    /**
     * Get customers list with pagination
     */
    public function customers(Request $request)
    {
        $user = Auth::user();
        $query = Customer::where('business_id', $user->business_id);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('name')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $customers
        ]);
    }

    /** 
     * Create a new customer
     */
    public function createCustomer(Request $request)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'business_id' => $user->business_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }

    /**
     * Get products list with pagination
     */
    public function products(Request $request)
    {
        $user = Auth::user();
        $query = Inventory::where('branch_id', $user->branch_id)
            ->with('category');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('in_stock')) {
            $query->where('quantity', '>', 0);
        }

        $products = $query->orderBy('name')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Get categories list
     */
    public function categories()
    {
        $user = Auth::user();
        $categories = Category::where('business_id', $user->business_id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }
} 