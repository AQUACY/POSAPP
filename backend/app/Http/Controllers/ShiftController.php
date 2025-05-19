<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function getCurrentShift(Request $request, $businessId, $branchId)
    {
        $shift = Shift::where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => $shift,
            'message' => $shift ? 'Current shift found' : 'No open shift found'
        ]);
    }

    public function openShift(Request $request, $businessId, $branchId)
    {
        $request->validate([
            'opening_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        // Check if user already has an open shift
        $existingShift = Shift::where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an open shift'
            ], 400);
        }

        $shift = Shift::create([
            'business_id' => $businessId,
            'branch_id' => $branchId,
            'user_id' => Auth::id(),
            'opening_amount' => $request->opening_amount,
            'notes' => $request->notes,
            'opened_at' => now(),
            'status' => 'open'
        ]);

        return response()->json([
            'success' => true,
            'data' => $shift,
            'message' => 'Shift opened successfully'
        ]);
    }

    public function closeShift(Request $request, $businessId, $branchId)
    {
        $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $shift = Shift::where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'No open shift found'
            ], 404);
        }

        // Calculate expected amount and difference
        $totalSales = DB::table('sales')
            ->where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->where('cashier_id', Auth::id())
            ->where('created_at', '>=', $shift->opened_at)
            ->sum('total_amount');

        $expectedAmount = $shift->opening_amount + $totalSales;
        $difference = $request->closing_amount - $expectedAmount;

        $shift->update([
            'closing_amount' => $request->closing_amount,
            'expected_amount' => $expectedAmount,
            'difference' => $difference,
            'notes' => $request->notes,
            'closed_at' => now(),
            'status' => 'closed'
        ]);

        return response()->json([
            'success' => true,
            'data' => $shift,
            'message' => 'Shift closed successfully'
        ]);
    }

    public function getShiftHistory(Request $request, $businessId, $branchId)
    {
        $query = Shift::where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->with(['user']);

        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->where('opened_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('opened_at', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'opened_at');
        $sortDesc = $request->get('sort_desc', true);
        $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

        // Get paginated results
        $perPage = $request->get('per_page', 10);
        $shifts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $shifts->items(),
            'total' => $shifts->total(),
            'message' => 'Shift history retrieved successfully'
        ]);
    }
} 