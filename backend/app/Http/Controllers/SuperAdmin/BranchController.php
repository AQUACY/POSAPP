<?php

use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\SuperAdmin\BaseController;
class BranchController extends BaseController
{
    /**
     * Display a listing of the branches for a specific business.
     *
     * @param int $businessId
     * @return JsonResponse
     */
    public function index($businessId): JsonResponse
    {
        $branches = Branch::where('business_id', $businessId)->get();
        return response()->json(['data' => $branches], 200);
    }

    /**
     * Store a newly created branch for a specific business.
     *
     * @param Request $request
     * @param int $businessId
     * @return JsonResponse
     */
    public function store(Request $request, $businessId): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|max:255',
        ]);

        $branch = Branch::create(array_merge($validatedData, ['business_id' => $businessId]));

        return response()->json(['data' => $branch, 'message' => 'Branch created successfully'], 201);
    }

    /**
     * Display the specified branch for a specific business.
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function show($businessId, $branchId): JsonResponse
    {
        $branch = Branch::where('business_id', $businessId)->findOrFail($branchId);
        return response()->json(['data' => $branch], 200);
    }

    /**
     * Update the specified branch for a specific business.
     *
     * @param Request $request
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function update(Request $request, $businessId, $branchId): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:15',
            'email' => 'sometimes|required|email|max:255',
        ]);

        $branch = Branch::where('business_id', $businessId)->findOrFail($branchId);
        $branch->update($validatedData);

        return response()->json(['data' => $branch, 'message' => 'Branch updated successfully'], 200);
    }

    /**
     * Remove the specified branch for a specific business.
     *
     * @param int $businessId
     * @param int $branchId
     * @return JsonResponse
     */
    public function destroy($businessId, $branchId): JsonResponse
    {
        $branch = Branch::where('business_id', $businessId)->findOrFail($branchId);
        $branch->delete();

        return response()->json(['message' => 'Branch deleted successfully'], 200);
    }
}

