<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Sale;
use App\Models\User;
use App\Models\StockChange;
use App\Models\InventoryActivity;
use App\Http\Resources\Admin\BusinessResource;
use App\Http\Resources\Admin\BranchResource;
use App\Http\Resources\Admin\InventoryResource;
use App\Http\Resources\Admin\CategoryResource;
use App\Http\Resources\Admin\WarehouseResource;
use App\Http\Resources\Admin\SaleResource;
use App\Http\Resources\Admin\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\StockRequest;

class AdminController extends Controller
{
    // Business Management
    public function createBusiness(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:businesses,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $business = Business::create($request->all());
        return response()->json(new BusinessResource($business), 201);
    }

    public function updateBusiness(Request $request, $id)
    {
        $business = Business::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:businesses,email,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $business->update($request->all());
        return response()->json(new BusinessResource($business));
    }

    public function getBusiness($id)
    {
        $business = Business::with(['branches', 'staff', 'inventory', 'sales'])->findOrFail($id);
        return response()->json(new BusinessResource($business));
    }

    public function listBusinesses()
    {
        $businesses = Business::with(['branches', 'staff', 'inventory', 'sales'])->get();
        return response()->json(BusinessResource::collection($businesses));
    }


    // Branch Management
    public function createBranch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:branches,email',
            'business_id' => 'required|exists:businesses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $branch = Branch::create($request->all());
        return response()->json(new BranchResource($branch), 201);
    }

    public function updateBranch(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:branches,email,' . $id,
            'business_id' => 'sometimes|required|exists:businesses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $branch->update($request->all());
        return response()->json(new BranchResource($branch));
    }

    public function getBranch($id)
    {
        $branch = Branch::with(['business', 'staff', 'inventory', 'sales'])->findOrFail($id);
        return response()->json(new BranchResource($branch));
    }

    public function listBranches(Request $request)
    {
        $businessId = $request->route('businessId');
        
        $branches = Branch::where('business_id', $businessId)
            ->with(['business', 'staff', 'inventory', 'sales'])
            ->get();
            
        return response()->json(BranchResource::collection($branches));
    }

    // Inventory Management
    public function createInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'required|string|unique:inventories,sku',
            'barcode' => 'nullable|string|unique:inventories,barcode',
            'quantity' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date',
            'unit_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'business_id' => 'required|exists:businesses,id',
            'branch_id' => 'nullable|exists:branches,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // Create inventory with default values for nullable fields
            $inventoryData = array_merge($request->all(), [
                'branch_id' => $request->branch_id ?? null,
                'sync_status' => 'pending'
            ]);

            $inventory = Inventory::create($inventoryData);

            // If branch_id is provided, create initial stock activity
            if ($request->branch_id) {
                InventoryActivity::create([
                    'inventory_id' => $inventory->id,
                    'user_id' => auth()->id(),
                    'branch_id' => $request->branch_id,
                    'action_type' => 'in',
                    'quantity' => $request->quantity,
                    'old_quantity' => 0,
                    'new_quantity' => $request->quantity,
                    'unit_price' => $request->unit_price,
                    'notes' => 'Initial stock'
                ]);
            }

            return response()->json(new InventoryResource($inventory), 201);
        } catch (\Exception $e) {
            \Log::error('Inventory Creation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create inventory item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateInventory(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'sku' => 'sometimes|required|string|unique:inventories,sku,' . $id,
            'barcode' => 'nullable|string|unique:inventories,barcode,' . $id,
            'quantity' => 'sometimes|required|integer|min:0',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'cost_price' => 'sometimes|required|numeric|min:0',
            'reorder_level' => 'sometimes|required|integer|min:0',
            'category_id' => 'sometimes|required|exists:categories,id',
            'business_id' => 'sometimes|required|exists:businesses,id',
            'branch_id' => 'sometimes|required|exists:branches,id',
            'warehouse_id' => 'sometimes|required|exists:warehouses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $inventory->update($request->all());
        return response()->json(new InventoryResource($inventory));
    }

    public function getInventory($id)
    {
        $inventory = Inventory::with(['category', 'business', 'branch'])->findOrFail($id);
        return response()->json(new InventoryResource($inventory));
    }

    public function listInventoryofBranch(Request $request)
    {
        $branchId = $request->route('branchId');
        $inventory = Inventory::with(['category', 'business', 'branch'])
            ->where('branch_id', $branchId)
            ->get();
        return response()->json(InventoryResource::collection($inventory));
    }

    public function listInventoryofBusiness(Request $request)
    {
        $businessId = $request->route('businessId');
        $query = Inventory::with(['category', 'business', 'branch'])
            ->where('business_id', $businessId);

        // Apply search filter if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Get total count before pagination
        $total = $query->count();

        // Apply sorting
        if ($request->has('sort_by')) {
            $direction = $request->descending ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Apply pagination
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $inventory = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => InventoryResource::collection($inventory),
            'total' => $total
        ]);
    }

    public function listInventoryofWarehouse(Request $request)
    {
        $businessId = $request->route('businessId');
        $inventory = Inventory::with(['category', 'business', 'branch'])
            ->where('business_id', $businessId)
            ->whereNotNull('warehouse_id')
            ->get();
        return response()->json(InventoryResource::collection($inventory));
    }

    public function deleteInventory($id)
    {
        $inventory = Inventory::findOrFail($id);
        $inventory->delete();
        return response()->json(null, 204);
    }

    public function updateStock(Request $request, $id)
    {
        $inventory = Inventory::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $inventory->update($request->all());
        return response()->json(new InventoryResource($inventory));
    }

    // Warehouse Management
    public function createWarehouse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string', 
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:warehouses,email',
            'business_id' => 'required|exists:businesses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $warehouse = Warehouse::create($request->all());
        return response()->json(new WarehouseResource($warehouse), 201);
    }

    public function updateWarehouse(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
            $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:warehouses,email,' . $id,
            'business_id' => 'sometimes|required|exists:businesses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        
        $warehouse->update($request->all());
        return response()->json(new WarehouseResource($warehouse));
    }

    public function getWarehouse($id)
    {   
        $warehouse = Warehouse::with(['business'])
            ->withCount(['inventory', 'stockRequests'])
            ->findOrFail($id);
        return response()->json(new WarehouseResource($warehouse));
    }

    public function listWarehouses()    
    {
        $warehouses = Warehouse::with(['business'])
            ->withCount(['inventories', 'stockRequests'])
            ->get();
        return response()->json(WarehouseResource::collection($warehouses));
    }

    public function listWarehousesofBusiness(Request $request)
    {
        $businessId = $request->route('businessId');
        $warehouses = Warehouse::with(['business'])
            ->where('business_id', $businessId)
            ->get();
        return response()->json(WarehouseResource::collection($warehouses));
    }

    public function deleteWarehouse($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();
        return response()->json(null, 204);
    }
    
    // Category Management
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'business_id' => 'required|exists:businesses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $category = Category::create($request->all());
        return response()->json(new CategoryResource($category), 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'business_id' => 'sometimes|required|exists:businesses,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $category->update($request->all());
        return response()->json(new CategoryResource($category));
    }

    public function getCategory($id)
    {
        $category = Category::with(['business', 'inventories'])->findOrFail($id);
        return response()->json(new CategoryResource($category));
    }

    public function listCategoriesofBusiness(Request $request)
    {
        $businessId = $request->route('businessId');
        $query = Category::with(['business', 'inventories'])
            ->where('business_id', $businessId);

        // Apply search filter if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get total count before pagination
        $total = $query->count();

        // Apply sorting
        if ($request->has('sort_by')) {
            $direction = $request->descending ? 'desc' : 'asc';
            $query->orderBy($request->sort_by, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Apply pagination
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $categories = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => CategoryResource::collection($categories),
            'total' => $total
        ]);
    }

    public function listCategoriesofBranch(Request $request)
    {
        $branchId = $request->route('branchId');
        $categories = Category::with(['business', 'inventories'])
            ->where('branch_id', $branchId)
            ->get();
    }

    // Staff Management
    public function createStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:inventory_clerk,cashier,branch_manager',
            'business_id' => 'required|exists:businesses,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'business_id' => $request->business_id,
            'branch_id' => $request->branch_id,
        ]);

        return response()->json(new UserResource($user), 201);
    }

    public function updateStaff(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|in:inventory_clerk,cashier,branch_manager',
            'business_id' => 'sometimes|required|exists:businesses,id',
            'branch_id' => 'sometimes|required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $data = $request->all();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return response()->json(new UserResource($user));
    }

    public function getStaff($id)
    {
        $user = User::with(['business', 'branch'])->findOrFail($id);
        return response()->json(new UserResource($user));
    }

    public function listStaffofBranch()
    {
        $users = User::with(['business', 'branch'])
            ->whereIn('role', ['inventory_clerk', 'cashier', 'branch_manager'])
            ->where('business_id', request()->route('businessId'))
            ->where('branch_id', request()->route('branchId'))
            ->get();
        return response()->json(UserResource::collection($users));
    }

    public function listStaffofBusiness()
    {
        $users = User::with(['business', 'branch'])
            ->whereIn('role', ['inventory_clerk', 'cashier', 'branch_manager'])
            ->where('business_id', request()->route('businessId'))
            ->get();
        return response()->json(UserResource::collection($users));
    }

    // Sales Management
    public function getSale($id)
    {
        $sale = Sale::with(['business', 'branch', 'cashier', 'items', 'customer'])->findOrFail($id);
        return response()->json(new SaleResource($sale));
    }

    public function listSales()
    {
        $sales = Sale::with(['business', 'branch', 'cashier', 'items', 'customer'])->get();
        return response()->json(SaleResource::collection($sales));
    }

    
    // Reports
    public function getBusinessPerformance($businessId)
    {
        $business = Business::with(['sales', 'branches'])->findOrFail($businessId);
        
        $performance = [
            'total_sales' => $business->sales->sum('total_amount'),
            'total_branches' => $business->branches->count(),
            'average_sale_per_branch' => $business->branches->avg(function($branch) {
                return $branch->sales->sum('total_amount');
            }),
            'top_selling_products' => DB::table('sale_items')
                ->join('inventories', 'sale_items.inventory_id', '=', 'inventories.id')
                ->where('inventories.business_id', $businessId)
                ->select('inventories.name', DB::raw('SUM(sale_items.quantity) as total_quantity'))
                ->groupBy('inventories.id', 'inventories.name')
                ->orderBy('total_quantity', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($performance);
    }

    public function getBranchPerformance($branchId)
    {
        $branch = Branch::with(['sales', 'staff'])->findOrFail($branchId);
        
        $performance = [
            'total_sales' => $branch->sales->sum('total_amount'),
            'total_staff' => $branch->staff->count(),
            'average_sale_per_staff' => $branch->staff->avg(function($staff) {
                return $staff->sales->sum('total_amount');
            }),
            'inventory_status' => [
                'total_items' => $branch->inventory->count(),
                'low_stock_items' => $branch->inventory->where('quantity', '<=', 'reorder_level')->count(),
            ],
        ];

        return response()->json($performance);
    }

    /**
     * Add stock to an inventory item
     */
    public function addStock(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'change_type' => 'required|in:addition,reduction,adjustment'
        ]);

        $inventory = Inventory::findOrFail($id);
        $oldQuantity = $inventory->quantity;
        
        try {
            DB::beginTransaction();

            // Create stock change record
            $stockChange = StockChange::create([
                'inventory_id' => $id,
                'user_id' => auth()->id(),
                'quantity' => $request->quantity,
                'change_type' => $request->change_type,
                'reason' => $request->reason
            ]);

            // Update inventory quantity based on change type
            switch ($request->change_type) {
                case 'addition':
                    $inventory->quantity += $request->quantity;
                    break;
                case 'reduction':
                    $inventory->quantity = max(0, $inventory->quantity - $request->quantity);
                    break;
                case 'adjustment':
                    $inventory->quantity = $request->quantity;
                    break;
            }

            $inventory->save();

            // Record in inventory activity
            InventoryActivity::create([
                'inventory_id' => $inventory->id,
                'user_id' => auth()->id(),
                'branch_id' => $inventory->branch_id,
                'action_type' => $request->change_type === 'addition' ? 'in' : ($request->change_type === 'reduction' ? 'out' : 'adjustment'),
                'quantity' => $request->quantity,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $inventory->quantity,
                'unit_price' => $inventory->unit_price,
                'notes' => $request->reason
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Stock updated successfully',
                'data' => [
                    'inventory' => $inventory,
                    'stock_change' => $stockChange
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock history for an inventory item
     */
    public function getStockHistory($id)
    {
        $stockChanges = StockChange::where('inventory_id', $id)
            ->with(['user:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($stockChanges);
    }

    // Stock Request Management
    public function getStockRequests()
    {
        $stockRequests = StockRequest::with([
            'branch', 
            'warehouse', 
            'items', 
            'requestedBy',
            'items.inventory' => function($query) {
                $query->where('warehouse_id', '!=', null);
            }
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($stockRequests);
    }

    public function getStockRequestDetails($id)
    {
        $stockRequest = StockRequest::with([
            'branch', 
            'warehouse', 
            'items.inventory', 
            'requestedBy'
        ])->findOrFail($id);   
        return response()->json($stockRequest);
    }

    public function approveStockRequest(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:merge,new',
            'target_inventory_id' => 'required_if:action,merge|exists:inventories,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $stockRequest = StockRequest::with([
                'items.inventory' => function($query) {
                    $query->where('warehouse_id', '!=', null);
                }, 
                'branch', 
                'warehouse'
            ])->findOrFail($id);

            if ($stockRequest->status !== 'pending') {
                throw new \Exception('Stock request is not in pending status');
            }

            // Validate target inventory belongs to the correct branch if merging
            if ($request->action === 'merge' && $request->target_inventory_id) {
                $targetInventory = Inventory::findOrFail($request->target_inventory_id);
                if ($targetInventory->branch_id !== $stockRequest->branch_id) {
                    throw new \Exception('Target inventory does not belong to the requesting branch');
                }
            }

            foreach ($stockRequest->items as $item) {
                $warehouseInventory = $item->inventory;
                
                // Check if warehouse has sufficient stock
                if ($warehouseInventory->quantity < $item->quantity_requested) {
                    throw new \Exception("Insufficient stock in warehouse for item: {$warehouseInventory->name} (Available: {$warehouseInventory->quantity}, Requested: {$item->quantity_requested})");
                }

                try {
                    // Debit warehouse inventory
                    $warehouseInventory->quantity -= $item->quantity_requested;
                    $warehouseInventory->save();

                    // Record stock change for warehouse
                    StockChange::create([
                        'inventory_id' => $warehouseInventory->id,
                        'user_id' => auth()->id(),
                        'quantity' => -$item->quantity_requested,
                        'change_type' => 'reduction',
                        'reason' => "Stock transfer to branch {$stockRequest->branch->name}",
                        'business_id' => $stockRequest->business_id,
                        'branch_id' => $stockRequest->branch_id
                    ]);

                    if ($request->action === 'merge') {
                        // Add to existing branch inventory
                        $branchInventory = Inventory::findOrFail($request->target_inventory_id);
                        $branchInventory->quantity += $item->quantity_requested;
                        $branchInventory->save();

                        // Record stock change for branch
                        StockChange::create([
                            'inventory_id' => $branchInventory->id,
                            'user_id' => auth()->id(),
                            'quantity' => $item->quantity_requested,
                            'change_type' => 'addition',
                            'reason' => "Stock transfer from warehouse {$stockRequest->warehouse->name}",
                            'business_id' => $stockRequest->business_id,
                            'branch_id' => $stockRequest->branch_id
                        ]);
                    } else {
                        // Create new inventory item in branch
                        $newInventory = Inventory::create([
                            'name' => $warehouseInventory->name,
                            'description' => $warehouseInventory->description,
                            'sku' => $warehouseInventory->sku . '-' . $stockRequest->branch_id, // Append branch ID to make SKU unique
                            'barcode' => $warehouseInventory->barcode,
                            'quantity' => $item->quantity_requested,
                            'unit_price' => $warehouseInventory->unit_price,
                            'cost_price' => $warehouseInventory->cost_price,
                            'reorder_level' => $warehouseInventory->reorder_level,
                            'category_id' => $warehouseInventory->category_id,
                            'business_id' => $stockRequest->business_id,
                            'branch_id' => $stockRequest->branch_id,
                            'warehouse_id' => null
                        ]);

                        // Record stock change for new branch inventory
                        StockChange::create([
                            'inventory_id' => $newInventory->id,
                            'user_id' => auth()->id(),
                            'quantity' => $item->quantity_requested,
                            'change_type' => 'addition',
                            'reason' => "Initial stock from warehouse {$stockRequest->warehouse->name}",
                            'business_id' => $stockRequest->business_id,
                            'branch_id' => $stockRequest->branch_id
                        ]);
                    }

                    // Update stock request item
                    $item->update([
                        'quantity_approved' => $item->quantity_requested,
                        'quantity_fulfilled' => $item->quantity_requested
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Stock transfer error', [
                        'error' => $e->getMessage(),
                        'item' => $item->toArray(),
                        'warehouse_inventory' => $warehouseInventory->toArray()
                    ]);
                    throw new \Exception("Failed to process stock transfer: " . $e->getMessage());
                }
            }

            // Update stock request status
            $stockRequest->update([
                'status' => 'fulfilled',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'fulfilled_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Stock request approved and transferred successfully',
                'data' => $stockRequest->load(['items.inventory', 'branch', 'warehouse', 'approvedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Stock request approval error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_id' => $id,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve stock request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rejectStockRequest($id)
    {
        $stockRequest = StockRequest::findOrFail($id);
        $stockRequest->status = 'rejected';
        $stockRequest->save();

        return response()->json($stockRequest);
    }
} 