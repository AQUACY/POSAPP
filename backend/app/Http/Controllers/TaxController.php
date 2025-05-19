<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->business_id) {
            return response()->json(['message' => 'User not associated with any business'], 400);
        }

        $taxes = Tax::where('business_id', $user->business_id)
            ->orderBy('order')
            ->get();

        return response()->json(['taxes' => $taxes]);
    }

    public function store(Request $request, $businessId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $business = Business::find($businessId);
        if (!$business) {
            return response()->json(['message' => 'Business not found'], 404);
        }

        $tax = Tax::create([
            'business_id' => $business->id,
            'name' => $request->name,
            'rate' => $request->rate,
            'order' => $request->order,
        ]);

        return response()->json(['tax' => $tax], 201);
    }

    public function update(Request $request, Tax $tax)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tax->update($request->all());
        return response()->json(['tax' => $tax]);
    }

    public function destroy(Tax $tax)
    {
        $tax->delete();
        return response()->json(null, 204);
    }

    public function toggleStatus(Tax $tax)
    {
        $tax->update(['is_active' => !$tax->is_active]);
        return response()->json(['tax' => $tax]);
    }
} 