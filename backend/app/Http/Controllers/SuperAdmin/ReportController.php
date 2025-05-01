<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\BaseController;
use App\Services\SuperAdmin\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get sales report
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSalesReport(Request $request): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date']);
        $data = $this->reportService->getSalesReport($filters);
        
        return $this->sendResponse($data, 'Sales report retrieved successfully');
    }

    /**
     * Get revenue report
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueReport(Request $request): JsonResponse
    {
        $filters = $request->only(['start_date', 'end_date']);
        $data = $this->reportService->getRevenueReport($filters);
        
        return $this->sendResponse($data, 'Revenue report retrieved successfully');
    }

    /**
     * Get business performance report
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getBusinessReport(Request $request): JsonResponse
    {
        $filters = $request->only(['status']);
        $data = $this->reportService->getBusinessReport($filters);
        
        return $this->sendResponse($data, 'Business report retrieved successfully');
    }
} 