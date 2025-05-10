<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\BaseController;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use App\Models\Category;
use App\Models\StockRequest;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class BranchManagerController extends BaseController
{
    /**
     * Get branch manager dashboard data
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function dashboard(int $businessId, int $branchId): JsonResponse
    {
        $branch = Branch::where('id', $branchId)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $dashboardData = [
            'branch' => $branch,
            'stats' => [
                'total_sales' => Sale::where('branch_id', $branchId)->sum('total_amount'),
                'total_customers' => Customer::where('branch_id', $branchId)->count(),
                'total_staff' => User::where('branch_id', $branchId)->count(),
                'low_stock_items' => Inventory::where('branch_id', $branchId)
                    ->where('quantity', '<', DB::raw('reorder_level'))
                    ->count()
            ],
            'recent_sales' => Sale::where('branch_id', $branchId)
                ->with(['customer', 'items.inventory'])
                ->latest()
                ->take(5)
                ->get()
        ];

        return $this->sendResponse($dashboardData, 'Branch manager dashboard data retrieved successfully');
    }

    /**
     * Get branch reports
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function reports(int $businessId, int $branchId): JsonResponse
    {
        $reports = [
            'sales_by_date' => Sale::where('branch_id', $branchId)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->take(30)
                ->get(),
            'top_products' => Inventory::where('branch_id', $branchId)
                ->withCount('saleItems')
                ->orderBy('sale_items_count', 'desc')
                ->take(10)
                ->get(),
            'customer_stats' => [
                'total' => Customer::where('branch_id', $branchId)->count(),
                'new_this_month' => Customer::where('branch_id', $branchId)
                    ->whereMonth('created_at', now()->month)
                    ->count()
            ]
        ];

        return $this->sendResponse($reports, 'Branch reports retrieved successfully');
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

        return $this->sendResponse($branch, 'Branch details retrieved successfully');
    }

    /**
     * Update branch details
     *
     * @param Request $request
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function updateBranchDetails(Request $request, int $businessId, int $branchId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]*$/',
            'email' => ['required', 'email', 'max:255', Rule::unique('branches')->ignore($branchId)],
            'settings' => 'nullable|array',
            'settings.*' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $branch = Branch::where('id', $branchId)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $branch->update($validated);

        return $this->sendResponse($branch, 'Branch details updated successfully');
    }

    /**
     * Get inventory
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function getInventory(int $businessId, int $branchId): JsonResponse
    {
        $inventory = Inventory::where('branch_id', $branchId)
            ->where('business_id', $businessId)
            ->with(['category'])
            ->paginate(20);

        return $this->sendResponse($inventory, 'Inventory retrieved successfully');
    }

    /**
     * Get warehouse
     *
     * @param int $businessId
     * @return JsonResponse
     */
    public function getWarehouse(int $businessId): JsonResponse
    {
        $warehouse = Warehouse::where('business_id', $businessId)
            ->firstOrFail();

        return $this->sendResponse($warehouse, 'Warehouse retrieved successfully');
    }

    // Get inventory of warehouse
    public function getInventoryofWarehouse(int $businessId, int $branchId, int $id): JsonResponse
    {
        $inventory = Inventory::where('warehouse_id', $id)
            ->where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->get();

        return $this->sendResponse($inventory, 'Inventory of warehouse retrieved successfully');
    }

    /**
     * Update inventory
     *
     * @param Request $request
     * @param int $businessId
     * @param int $branchId
     * @param int $id
     * @return JsonResponse
     */
    public function updateInventory(Request $request, int $businessId, int $branchId, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => ['required', 'string', 'max:50', Rule::unique('inventories')->ignore($id)],
            'barcode' => ['nullable', 'string', 'max:50', Rule::unique('inventories')->ignore($id)],
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0|decimal:0,2',
            'cost_price' => 'required|numeric|min:0|decimal:0,2',
            'reorder_level' => 'required|numeric|min:0',
            'category_id' => ['required', 'exists:categories,id', function ($attribute, $value, $fail) use ($businessId) {
                if (!Category::where('id', $value)->where('business_id', $businessId)->exists()) {
                    $fail('The selected category does not belong to this business.');
                }
            }],
            'is_non_refundable' => 'boolean',
            'requires_condition_check' => 'boolean',
            'condition_notes' => 'nullable|string|max:1000',
            'refund_restriction_amount' => 'nullable|numeric|min:0|decimal:0,2',
            'refund_restriction_days' => 'nullable|integer|min:0|max:365'
        ]);

        $inventory = Inventory::where('id', $id)
            ->where('branch_id', $branchId)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $inventory->update($validated);

        return $this->sendResponse($inventory, 'Inventory updated successfully');
    }

    /**
     * Create a new inventory item
     *
     * @param Request $request
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function createInventoryItem(Request $request, int $businessId, int $branchId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|max:50|unique:inventories,sku',
            'barcode' => 'nullable|string|max:50|unique:inventories,barcode',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0|decimal:0,2',
            'cost_price' => 'required|numeric|min:0|decimal:0,2',
            'reorder_level' => 'required|numeric|min:0',
            'category_id' => ['required', 'exists:categories,id', function ($attribute, $value, $fail) use ($businessId) {
                if (!Category::where('id', $value)->where('business_id', $businessId)->exists()) {
                    $fail('The selected category does not belong to this business.');
                }
            }],
            'is_non_refundable' => 'boolean',
            'requires_condition_check' => 'boolean',
            'condition_notes' => 'nullable|string|max:1000',
            'refund_restriction_amount' => 'nullable|numeric|min:0|decimal:0,2',
            'refund_restriction_days' => 'nullable|integer|min:0|max:365'
        ]);

        $inventory = Inventory::create([
            ...$validated,
            'business_id' => $businessId,
            'branch_id' => $branchId
        ]);

        return $this->sendResponse($inventory, 'Inventory item created successfully');
    }

    /**
     * Delete inventory item
     *
     * @param int $businessId
     * @param int $branchId
     * @param int $id
     * @return JsonResponse
     */
    public function deleteInventoryItem(int $businessId, int $branchId, int $id): JsonResponse
    {
        $inventory = Inventory::where('id', $id)
            ->where('branch_id', $branchId)
            ->where('business_id', $businessId)
            ->firstOrFail();

        $inventory->delete();

        return $this->sendResponse(null, 'Inventory item deleted successfully');
    }

    /**
     * Get sales
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function getSales(int $businessId, int $branchId): JsonResponse
    {
        $sales = Sale::where('branch_id', $branchId)
            ->with(['customer', 'items.inventory'])
            ->latest()
            ->paginate(20);

        return $this->sendResponse($sales, 'Sales retrieved successfully');
    }

    /**
     * Get sales summary
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function getSalesSummary(int $businessId, int $branchId): JsonResponse
    {
        $summary = [
            'today' => Sale::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->sum('total_amount'),
            'this_week' => Sale::where('branch_id', $branchId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('total_amount'),
            'this_month' => Sale::where('branch_id', $branchId)
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount'),
            'total_transactions' => Sale::where('branch_id', $branchId)
                ->whereMonth('created_at', now()->month)
                ->count()
        ];

        return $this->sendResponse($summary, 'Sales summary retrieved successfully');
    }

    /**
     * Get customers
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function getCustomers(int $businessId, int $branchId): JsonResponse
    {
        $customers = Customer::where('branch_id', $branchId)
            ->with(['sales' => function ($query) {
                $query->latest()->take(5);
            }])
            ->paginate(20);

        return $this->sendResponse($customers, 'Customers retrieved successfully');
    }

    /**
     * Get staff
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function getStaff(int $businessId, int $branchId): JsonResponse
    {
        $staff = User::where('branch_id', $branchId)
            ->where('role', 'cashier')
            ->get();

        return $this->sendResponse($staff, 'Staff retrieved successfully');
    }

    /**
     * Create cashier
     *
     * @param Request $request
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function createCashier(Request $request, int $businessId, int $branchId): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]*$/'
        ]);

        $cashier = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'role' => 'cashier',
            'branch_id' => $branchId,
            'business_id' => $businessId
        ]);

        return $this->sendResponse($cashier, 'Cashier created successfully');
    }

    /**
     * Update cashier
     *
     * @param Request $request
     * @param int $businessId
     * @param int $branchId
     * @param int $id
     * @return JsonResponse
     */
    public function updateCashier(Request $request, int $businessId, int $branchId, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($id)],
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]*$/',
            'is_active' => 'boolean'
        ]);

        $cashier = User::where('id', $id)
            ->where('branch_id', $branchId)
            ->where('role', 'cashier')
            ->firstOrFail();

        $cashier->update($validated);

        return $this->sendResponse($cashier, 'Cashier updated successfully');
    }

    /**
     * Get stock requests for the branch
     *
     * @param int $businessId
     * @param int $branchId
     * @param int $warehouseId
     * @return JsonResponse
     */
    public function getStockRequests(int $businessId, int $branchId, int $warehouseId): JsonResponse
    {
        try {
            $stockRequests = StockRequest::where('branch_id', $branchId)
                ->where('business_id', $businessId)
                ->where('warehouse_id', $warehouseId)
                ->with([
                    'items' => function($query) {
                        $query->with(['inventories' => function($query) {
                            $query->select('id', 'name', 'quantity', 'unit');
                        }]);
                    },
                    'warehouse',
                    'requestedBy',
                    'approvedBy'
                ])
                ->latest()
                ->paginate(20);

            // Transform the response to include available quantity
            $stockRequests->getCollection()->transform(function ($request) {
                $request->items->transform(function ($item) {
                    if ($item->inventory) {
                        $item->name = $item->inventory->name;
                        $item->available_quantity = $item->inventory->quantity;
                        $item->unit = $item->inventory->unit;
                    }
                    return $item;
                });
                return $request;
            });

            return $this->sendResponse($stockRequests, 'Stock requests retrieved successfully');
        } catch (\Exception $e) {
            \Log::error('Error fetching stock requests', [
                'error' => $e->getMessage(),
                'business_id' => $businessId,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId
            ]);
            
            return $this->sendError('Failed to retrieve stock requests', [], 500);
        }
    }

    /**
     * Create a new stock request
     *
     * @param Request $request
     * @param int $businessId
     * @param int $branchId
     * @param int $warehouseId
     * @return JsonResponse
     */
    public function createStockRequest(Request $request, int $businessId, int $branchId, int $warehouseId): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id', function ($attribute, $value, $fail) use ($businessId) {
                if (!Warehouse::where('id', $value)->where('business_id', $businessId)->exists()) {
                    $fail('The selected warehouse does not belong to this business.');
                }
            }],
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => ['required', 'exists:inventories,id', function ($attribute, $value, $fail) use ($businessId) {
                if (!Inventory::where('id', $value)->where('business_id', $businessId)->exists()) {
                    $fail('The selected inventory item does not belong to this business.');
                }
            }],
            'items.*.quantity_requested' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();

        try {
            $stockRequest = StockRequest::create([
                'business_id' => $businessId,
                'branch_id' => $branchId,
                'warehouse_id' => $warehouseId,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'requested_by' => auth()->id()
            ]);

            foreach ($validated['items'] as $item) {
                $stockRequest->items()->create([
                    'inventory_id' => $item['inventory_id'],
                    'quantity_requested' => $item['quantity_requested'],
                    'notes' => $item['notes'] ?? null
                ]);
            }

            DB::commit();

            return $this->sendResponse(
                $stockRequest->load(['items.inventory', 'warehouse', 'requestedBy']),
                'Stock request created successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get stock request details
     *
     * @param int $businessId
     * @param int $branchId
     * @param int $id
     * @return JsonResponse
     */
    public function getStockRequestDetails(int $businessId, int $branchId, int $id): JsonResponse
    {
        $stockRequest = StockRequest::where('id', $id)
            ->where('branch_id', $branchId)
            ->where('business_id', $businessId)
            ->with(['items.inventory', 'warehouse', 'requestedBy', 'approvedBy'])
            ->firstOrFail();

        return $this->sendResponse($stockRequest, 'Stock request details retrieved successfully');
    }

    /**
     * Cancel a stock request
     *
     * @param int $businessId
     * @param int $branchId
     * @param int $id
     * @return JsonResponse
     */
    public function cancelStockRequest(int $businessId, int $branchId, int $id): JsonResponse
    {
        $stockRequest = StockRequest::where('id', $id)
            ->where('branch_id', $branchId)
            ->where('business_id', $businessId)
            ->where('status', 'pending')
            ->firstOrFail();

        $stockRequest->update(['status' => 'rejected']);

        return $this->sendResponse($stockRequest, 'Stock request cancelled successfully');
    }
} 