<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends BaseController
{
    /**
     * Display a listing of the branches.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // TODO: Implement index logic
        return $this->sendResponse([], 'Branches retrieved successfully');
    }

    /**
     * Store a newly created branch in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // TODO: Implement store logic
        return $this->sendResponse([], 'Branch created successfully');
    }

    /**
     * Display the specified branch.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        // TODO: Implement show logic
        return $this->sendResponse([], 'Branch retrieved successfully');
    }

    /**
     * Update the specified branch in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // TODO: Implement update logic
        return $this->sendResponse([], 'Branch updated successfully');
    }

    /**
     * Remove the specified branch from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        // TODO: Implement destroy logic
        return $this->sendResponse([], 'Branch deleted successfully');
    }
} 