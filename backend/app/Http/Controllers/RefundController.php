<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Sale;
use App\Models\Inventory;
use App\Models\User;
use App\Notifications\RefundRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\RoleMiddleware;
use Carbon\Carbon;

class RefundController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware(RoleMiddleware::class . ':admin,branch_manager')->only(['approve']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'saleId' => 'required|exists:sales,id',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'reason' => 'required|string|min:7'
        ]);

        $sale = Sale::with('items.inventory')->findOrFail($request->saleId);

        // Check if sale is eligible for refund
        if (!$this->isEligibleForRefund($sale)) {
            return response()->json([
                'message' => 'This sale is not eligible for refund'
            ], 400);
        }

        // Check if sale is already refunded
        if ($sale->status === 'refunded') {
            return response()->json([
                'message' => 'This sale has already been refunded'
            ], 400);
        }

        // Check if sale is within refund time limit
        if (!$this->isWithinRefundTimeLimit($sale)) {
            return response()->json([
                'message' => 'This sale is outside the refund time limit'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $refund = Refund::create([
                'sale_id' => $sale->id,
                'user_id' => Auth::id(),
                'business_id' => $sale->business_id,
                'branch_id' => $sale->branch_id,
                'total_amount' => 0,
                'reason' => $request->reason,
                'status' => 'pending'
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $saleItem = $sale->items()->findOrFail($item['id']);
                
                if ($item['quantity'] > $saleItem->quantity) {
                    throw new \Exception("Refund quantity cannot exceed original sale quantity");
                }

                // Check if item is eligible for refund
                if (!$this->isItemEligibleForRefund($saleItem)) {
                    throw new \Exception("Item {$saleItem->inventory->name} is not eligible for refund");
                }

                $refundItem = RefundItem::create([
                    'refund_id' => $refund->id,
                    'sale_item_id' => $saleItem->id,
                    'inventory_id' => $saleItem->inventory_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $saleItem->unit_price,
                    'total_amount' => $saleItem->unit_price * $item['quantity']
                ]);

                $totalAmount += $refundItem->total_amount;
            }

            $refund->update(['total_amount' => $totalAmount]);

            // Notify managers and admins
            $this->notifyManagersAndAdmins($refund);

            DB::commit();

            return response()->json([
                'message' => 'Refund request submitted successfully. Managers have been notified.',
                'refund' => $refund->load('items')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request, $businessId, $refundId)
    {
        $request->validate([
            'approved' => 'required|boolean',
            'rejection_reason' => 'required_if:approved,false|string'
        ]);

        $refund = Refund::where('business_id', $businessId)
            ->where('id', $refundId)
            ->firstOrFail();

        // Check if user has permission for this business
        if (Auth::user()->role !== 'admin' && Auth::user()->business_id !== $businessId) {
            return response()->json(['message' => 'Unauthorized. You do not have permission for this business.'], 403);
        }

        try {
            DB::beginTransaction();

            if ($request->approved) {
                $refund->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id()
                ]);

                // Update inventory quantities
                foreach ($refund->items as $item) {
                    $inventory = Inventory::find($item->inventory_id);
                    $inventory->increment('quantity', $item->quantity);
                }

                // Update sale status
                $refund->sale->update(['status' => 'refunded']);
            } else {
                $refund->update([
                    'status' => 'rejected',
                    'approved_by' => Auth::id(),
                    'rejection_reason' => $request->rejection_reason
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => $request->approved ? 'Refund approved' : 'Refund rejected',
                'refund' => $refund->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process refund approval: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $refunds = Refund::with(['sale', 'user', 'approver', 'items.inventory'])
            ->latest()
            ->paginate(10);

        return response()->json($refunds);
    }

    
    // getrefund for branch manager 
    public function getRefundForBranchManager()
    {
        $refunds = Refund::with(['sale', 'user', 'approver', 'items.inventory'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return response()->json($refunds);
    }

    // get refund for branch
    public function getRefundforBranch(Request $request)
    {
        $refunds = Refund::with(['sale', 'user', 'approver', 'items.inventory'])
            ->where('branch_id', $request->branch_id)
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            }, function ($query) {
                return $query->where('status', 'pending');
            })
            ->latest()
            ->paginate(10);

        return response()->json($refunds);
    }

    // get refund for business
    public function getRefundforBusiness(Request $request, $businessId)
    {
        $query = Refund::with(['sale', 'user', 'approver', 'items.inventory', 'branch'])
            ->where('business_id', $businessId);

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->has('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                $query->whereBetween('created_at', [
                    Carbon::parse($dates[0])->startOfDay(),
                    Carbon::parse($dates[1])->endOfDay()
                ]);
            }
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('sale', function($q) use ($search) {
                    $q->where('sale_number', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Branch filter
        if ($request->has('branch')) {
            $query->where('branch_id', $request->branch);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $descending = $request->get('descending', true);
        $query->orderBy($sortBy, $descending ? 'desc' : 'asc');

        // Pagination
        $perPage = $request->get('per_page', 10);
        $refunds = $query->paginate($perPage);

        return response()->json($refunds);
    }
    

    private function isEligibleForRefund(Sale $sale)
    {
        // Check if sale is completed
        if ($sale->status !== 'completed') {
            return false;
        }

        // Check if sale is within refund time limit
        if (!$this->isWithinRefundTimeLimit($sale)) {
            return false;
        }

        return true;
    }

    private function isWithinRefundTimeLimit(Sale $sale)
    {
        $refundTimeLimit = config('pos.refund.time_limit', 30);
        return $sale->created_at->diffInDays(now()) <= $refundTimeLimit;
    }

    private function isItemEligibleForRefund($saleItem)
    {
        // Check if item is marked as non-refundable
        if ($saleItem->inventory->is_non_refundable) {
            return false;
        }

        // Check if item is in good condition (if condition check is required)
        if ($saleItem->inventory->requires_condition_check && !$this->isItemInGoodCondition($saleItem)) {
            return false;
        }

        // Check if item has specific refund restrictions
        if (!$saleItem->inventory->isEligibleForRefund()) {
            return false;
        }

        return true;
    }

    private function isItemInGoodCondition($saleItem)
    {
        if (!config('pos.refund.require_condition_check', false)) {
            return true;
        }

        // Implement your condition check logic here
        // This could include checking for damage, wear and tear, etc.
        return true; // Default to true, implement your logic
    }

    private function validateRefundAmount($sale, $totalAmount)
    {
        $maxPercentage = config('pos.refund.max_amount_percentage', 100);
        $maxAmount = $sale->final_amount * ($maxPercentage / 100);

        if ($totalAmount > $maxAmount) {
            throw new \Exception("Refund amount cannot exceed {$maxPercentage}% of the sale amount");
        }
    }

    private function requiresManagerApproval($totalAmount)
    {
        $approvalThreshold = config('pos.refund.require_approval_above', 1000);
        return $totalAmount > $approvalThreshold;
    }

    private function notifyManagersAndAdmins(Refund $refund)
    {
        // Get all managers and admins for the business
        $managersAndAdmins = User::whereIn('role', ['branch_manager', 'admin'])
            ->where('business_id', $refund->sale->business_id)
            ->get();

        // Send notification to each manager/admin
        foreach ($managersAndAdmins as $user) {
            $user->notify(new RefundRequestNotification($refund));
        }
    }
} 