<?php

namespace App\Http\Controllers\InventoryManager;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InventoryManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:inventory_manager');
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

        $inventory->update([
            'quantity' => $newQuantity
        ]);

        // TODO: Create inventory transaction record

        return response()->json([
            'status' => 'success',
            'message' => 'Inventory quantity updated successfully',
            'data' => [
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'item' => $inventory
            ]
        ]);
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
        $categories = Category::where('business_id', $user->business_id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }
} 