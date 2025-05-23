<?php

namespace App\Http\Controllers\InventoryManager;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Branch;
use App\Models\InventoryActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\RoleMiddleware;

class InventoryManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware(RoleMiddleware::class . ':inventory_clerk,branch_manager');
    }

    /**
     * Get inventory items with filters
     */
    public function getInventory(Request $request)
    {
        $user = Auth::user();
        $query = Inventory::where('branch_id', $user->branch_id)
            ->with(['category', 'branch']);

        // Apply filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->has('low_stock')) {
            $query->whereRaw('quantity <= reorder_level');
        }

        $inventory = $query->orderBy('name')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
    }

    /**
     * Get inventory item details
     */
    public function getInventoryItem($id)
    {
        $user = Auth::user();
        $item = Inventory::where('branch_id', $user->branch_id)
            ->with(['category', 'branch'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $item
        ]);
    }

    /**
     * Create new inventory item
     */
    public function createInventory(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'sku' => 'required|string|max:50|unique:inventories',
            'barcode' => 'required|string|max:50|unique:inventories',
            'quantity' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'expiry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $inventory = Inventory::create([
            ...$request->all(),
            'business_id' => $user->business_id,
            'branch_id' => $user->branch_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory item created successfully',
            'data' => $inventory
        ], 201);
    }

    /**
     * Update inventory item
     */
    public function updateInventory(Request $request, $id)
    {
        $user = Auth::user();
        $inventory = Inventory::where('branch_id', $user->branch_id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'sku' => 'sometimes|required|string|max:50|unique:inventories,sku,' . $id,
            'barcode' => 'sometimes|required|string|max:50|unique:inventories,barcode,' . $id,
            'quantity' => 'sometimes|required|integer|min:0',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'reorder_level' => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'expiry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $inventory->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory item updated successfully',
            'data' => $inventory
        ]);
    }

    /**
     * Update inventory quantity (stock in/out)
     */
    public function updateQuantity(Request $request, $id)
    {
        $user = Auth::user();
        $inventory = Inventory::where('branch_id', $user->branch_id)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer',
            'type' => 'required|in:in,out',
            'notes' => 'sometimes|string|max:255',
            'reference_type' => 'sometimes|string|max:50',
            'reference_id' => 'sometimes|string|max:50',
            'unit_price' => 'sometimes|numeric|min:0',
            'expiry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldQuantity = $inventory->quantity;
        $newQuantity = $request->type === 'in' 
            ? $oldQuantity + $request->quantity 
            : $oldQuantity - $request->quantity;

        if ($newQuantity < 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient stock for this operation'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update inventory quantity
            $inventory->update([
                'quantity' => $newQuantity,
                'unit_price' => $request->unit_price ?? $inventory->unit_price
            ]);

            // Record the activity
            InventoryActivity::create([
                'inventory_id' => $inventory->id,
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'action_type' => $request->type,
                'quantity' => $request->quantity,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'unit_price' => $request->unit_price ?? $inventory->unit_price,
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'notes' => $request->notes
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Inventory quantity updated successfully',
                'data' => [
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'item' => $inventory
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update inventory quantity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems()
    {
        $user = Auth::user();
        $items = Inventory::where('branch_id', $user->branch_id)
            ->whereRaw('quantity <= reorder_level')
            ->with('category')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $items
        ]);
    }

    /**
     * Get inventory summary
     */
    public function getInventorySummary()
    {
        $user = Auth::user();
        $summary = Inventory::where('branch_id', $user->branch_id)
            ->selectRaw('
                COUNT(*) as total_items,
                SUM(quantity) as total_quantity,
                SUM(quantity * unit_price) as total_value,
                COUNT(CASE WHEN quantity <= reorder_level THEN 1 END) as low_stock_items
            ')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    /**
     * Get categories
     */
    public function getCategories()
    {
        $user = Auth::user();
        
        // Get categories for the current business
        $categories = Category::where('business_id', $user->business_id)

            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

      /**
     * Get categories
     */
    public function getCategoriesforBranch()
    {
        $user = Auth::user();
        
        // Get categories for the current business
        $categories = Category::where('branch_id', $user->branch_id)

            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }


    /**
     * Get dashboard summary including all metrics
     */
    public function getDashboardSummary()
    {
        $user = Auth::user();
        
        // Get current summary
        $currentSummary = Inventory::where('branch_id', $user->branch_id)
            ->selectRaw('
                COUNT(*) as total_items,
                SUM(quantity) as total_quantity,
                SUM(quantity * unit_price) as total_value,
                COUNT(CASE WHEN quantity <= reorder_level THEN 1 END) as low_stock_items,
                COUNT(CASE WHEN expiry_date <= NOW() THEN 1 END) as expired_items
            ')
            ->first();

        // Get last month's summary for comparison
        $lastMonthSummary = Inventory::where('branch_id', $user->branch_id)
            ->whereDate('created_at', '<=', now()->subMonth())
            ->selectRaw('COUNT(*) as total_items')
            ->first();

        // Calculate percentage change
        $percentageChange = 0;
        if ($lastMonthSummary->total_items > 0) {
            $percentageChange = (($currentSummary->total_items - $lastMonthSummary->total_items) / $lastMonthSummary->total_items) * 100;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_items' => $currentSummary->total_items,
                'items_change' => round($percentageChange, 1),
                'low_stock_items' => $currentSummary->low_stock_items,
                'expired_items' => $currentSummary->expired_items,
                'stock_value' => round($currentSummary->total_value, 2)
            ]
        ]);
    }

    /**
     * Get recent inventory activities
     */
    public function getRecentActivities()
    {
        $user = Auth::user();
        
        $activities = InventoryActivity::where('branch_id', $user->branch_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $activities
        ]);
    }

    /**
     * Get stock alerts
     */
    public function getStockAlerts()
    {
        $user = Auth::user();
        
        $lowStockItems = Inventory::where('branch_id', $user->branch_id)
            ->whereRaw('quantity <= reorder_level')
            ->get();

        $alerts = [];
        foreach ($lowStockItems as $item) {
            $alerts[] = [
                'id' => $item->id,
                'icon' => $item->quantity === 0 ? 'error' : 'warning',
                'color' => $item->quantity === 0 ? 'negative' : 'warning',
                'title' => $item->quantity === 0 ? 'Out of Stock' : 'Low Stock Alert',
                'message' => "{$item->name} is " . ($item->quantity === 0 ? 'out of stock' : 'running low')
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $alerts
        ]);
    }

    /**
     * Get items nearing expiry
     */
    public function getExpiringItems()
    {
        $user = Auth::user();
        
        $expiringItems = Inventory::where('branch_id', $user->branch_id)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->orderBy('expiry_date')
            ->get();

        $items = $expiringItems->map(function ($item) {
            $daysLeft = now()->diffInDays($item->expiry_date);
            return [
                'id' => $item->id,
                'name' => $item->name,
                'daysLeft' => $daysLeft,
                'quantity' => $item->quantity
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items
        ]);
    }

    /**
     * Get expired items
     */
    public function getExpiredItems()
    {
        $user = Auth::user();
        
        $expiredItems = Inventory::where('branch_id', $user->branch_id)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now())
            ->with('category')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $expiredItems
        ]);
    }

        /**
     * Get branch details
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse 
     */
    public function getBranchDetails(int $businessId, int $branchId): JsonResponse
    {
        $branch = Branch::where('id', $branchId)
            ->where('business_id', $businessId)
            ->firstOrFail();
        
        return response()->json([
            'status' => 'success',
            'data' => $branch
        ]);
    }

    /**
     * Get inventory report with detailed analytics
     */
    public function getInventoryReport(Request $request)
    {
        try {
            $user = Auth::user();
            
            \Log::info('Inventory Report Request', [
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'date_from' => $request->from,
                'date_to' => $request->to
            ]);

            // Validate date range
            $validator = Validator::make($request->all(), [
                'from' => 'required|date|before_or_equal:today',
                'to' => 'required|date|after_or_equal:from|before_or_equal:today'
            ]);

            if ($validator->fails()) {
                \Log::warning('Inventory Report Validation Failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if branch exists
            $branch = Branch::where('id', $user->branch_id)->first();
            if (!$branch) {
                \Log::error('Branch not found', ['branch_id' => $user->branch_id]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Branch not found'
                ], 404);
            }

            // Get stock movements with inventory relationship
            $movements = InventoryActivity::where('branch_id', $user->branch_id)
                ->whereBetween('created_at', [$request->from, $request->to])
                ->with(['inventory' => function($query) {
                    $query->select('id', 'name');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('Inventory Movements Found', [
                'count' => $movements->count()
            ]);

            $movements = $movements->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'created_at' => $activity->created_at,
                    'inventory_name' => $activity->inventory ? $activity->inventory->name : 'Unknown Item',
                    'action_type' => $activity->action_type,
                    'quantity' => $activity->quantity,
                    'notes' => $activity->notes
                ];
            });

            // Calculate summary
            $summary = InventoryActivity::where('branch_id', $user->branch_id)
                ->whereBetween('created_at', [$request->from, $request->to])
                ->selectRaw('
                    COUNT(*) as totalChanges,
                    SUM(CASE WHEN action_type = "in" THEN quantity ELSE 0 END) as totalAdded,
                    SUM(CASE WHEN action_type = "out" THEN quantity ELSE 0 END) as totalRemoved,
                    SUM(CASE WHEN action_type = "in" THEN quantity ELSE -quantity END) as netChange
                ')
                ->first();

            \Log::info('Inventory Summary Calculated', [
                'summary' => $summary
            ]);

            // Get most active items with proper inventory relationship
            $mostActiveItems = DB::table('inventory_activities')
                ->join('inventories', 'inventory_activities.inventory_id', '=', 'inventories.id')
                ->where('inventory_activities.branch_id', $user->branch_id)
                ->whereBetween('inventory_activities.created_at', [$request->from, $request->to])
                ->select(
                    'inventories.id',
                    'inventories.name',
                    DB::raw('COUNT(*) as total_changes'),
                    DB::raw('SUM(CASE WHEN action_type = "in" THEN quantity ELSE -quantity END) as net_change')
                )
                ->groupBy('inventories.id', 'inventories.name')
                ->orderBy('total_changes', 'desc')
                ->limit(5)
                ->get();

            \Log::info('Most Active Items Found', [
                'count' => $mostActiveItems->count()
            ]);

            // Get low stock items
            $lowStockItems = Inventory::where('branch_id', $user->branch_id)
                ->whereRaw('quantity <= reorder_level')
                ->select('id', 'name', 'quantity', 'reorder_level')
                ->get();

            \Log::info('Low Stock Items Found', [
                'count' => $lowStockItems->count()
            ]);

            // Return empty data structure if no activities found
            if ($movements->isEmpty() && $mostActiveItems->isEmpty()) {
                \Log::info('No Inventory Activities Found for Date Range');
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'summary' => [
                            'totalChanges' => 0,
                            'totalAdded' => 0,
                            'totalRemoved' => 0,
                            'netChange' => 0
                        ],
                        'movements' => [],
                        'most_active_items' => [],
                        'low_stock_items' => $lowStockItems
                    ],
                    'message' => 'No inventory activities found for the selected date range'
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'summary' => [
                        'totalChanges' => $summary->totalChanges ?? 0,
                        'totalAdded' => $summary->totalAdded ?? 0,
                        'totalRemoved' => $summary->totalRemoved ?? 0,
                        'netChange' => $summary->netChange ?? 0
                    ],
                    'movements' => $movements,
                    'most_active_items' => $mostActiveItems,
                    'low_stock_items' => $lowStockItems
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Inventory Report Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate inventory report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 