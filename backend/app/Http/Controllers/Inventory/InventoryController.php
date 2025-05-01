<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends BaseController
{
    /**
     * Display a listing of the inventory items.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // TODO: Implement index logic
        return $this->sendResponse([], 'Inventory items retrieved successfully');
    }

    /**
     * Store a newly created inventory item in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // TODO: Implement store logic
        return $this->sendResponse([], 'Inventory item created successfully');
    }
} 