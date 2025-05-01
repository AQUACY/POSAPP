<?php

namespace App\Http\Controllers\BranchManager;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BranchManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:branch_manager');
    }

    /**
     * Get branch details
     */
    public function getBranchDetails()
    {
        $user = Auth::user();
        $branch = Branch::with('business')->findOrFail($user->branch_id);

        return response()->json([
            'status' => 'success',
            'data' => $branch
        ]);
    }

    /**
     * Update branch details
     */
    public function updateBranchDetails(Request $request)
    {
        $user = Auth::user();
        $branch = Branch::findOrFail($user->branch_id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|max:255',
            'settings' => 'sometimes|required|array',
            'is_active' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $branch->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Branch details updated successfully',
            'data' => $branch
        ]);
    }

    /**
     * Get branch inventory
     */
    public function getInventory(Request $request)
    {
        $user = Auth::user();
        $query = Inventory::where('branch_id', $user->branch_id)
            ->with('category');

        // Apply filters if provided
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

        $inventory = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $inventory
        ]);
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
            'quantity' => 'sometimes|required|integer|min:0',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'reorder_level' => 'sometimes|required|integer|min:0',
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
     * Get branch sales
     */
    public function getSales(Request $request)
    {
        $user = Auth::user();
        $query = Sale::where('branch_id', $user->branch_id)
            ->with(['customer', 'cashier', 'items.inventory']);

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
     * Get sales summary
     */
    public function getSalesSummary(Request $request)
    {
        $user = Auth::user();
        $query = Sale::where('branch_id', $user->branch_id);

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $summary = [
            'total_sales' => $query->count(),
            'total_amount' => $query->sum('final_amount'),
            'total_discount' => $query->sum('discount_amount'),
            'total_tax' => $query->sum('tax_amount'),
            'average_sale' => $query->avg('final_amount'),
        ];

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    /**
     * Get branch customers
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

        $customers = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $customers
        ]);
    }

    /**
     * Manage branch staff (cashiers)
     */
    public function getStaff()
    {
        $user = Auth::user();
        $staff = User::where('branch_id', $user->branch_id)
            ->where('role', 'cashier')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $staff
        ]);
    }

    /**
     * Create new cashier
     */
    public function createCashier(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $cashier = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'role' => 'cashier',
            'business_id' => $user->business_id,
            'branch_id' => $user->branch_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cashier created successfully',
            'data' => $cashier
        ], 201);
    }

    /**
     * Update cashier details
     */
    public function updateCashier(Request $request, $id)
    {
        $user = Auth::user();
        $cashier = User::where('branch_id', $user->branch_id)
            ->where('role', 'cashier')
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'sometimes|required|string|max:20',
            'is_active' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $cashier->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Cashier updated successfully',
            'data' => $cashier
        ]);
    }
} 