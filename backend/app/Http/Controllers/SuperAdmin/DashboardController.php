<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Services\SuperAdmin\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for handling super admin dashboard operations
 */
class DashboardController extends BaseController
{
    /**
     * @var DashboardService
     */
    protected DashboardService $dashboardService;

    /**
     * Constructor
     *
     * @param DashboardService $dashboardService
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get dashboard overview
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $data = [
            'platform_stats' => $this->dashboardService->getPlatformStats(),
            'business_growth' => $this->dashboardService->getBusinessGrowth(
                $request->input('months', 12)
            ),
            'user_growth' => $this->dashboardService->getUserGrowth(
                $request->input('months', 12)
            ),
            'top_businesses' => $this->dashboardService->getTopBusinesses(
                $request->input('limit', 5)
            ),
            'recent_activities' => $this->dashboardService->getRecentActivities(
                $request->input('limit', 10)
            ),
        ];

        return $this->sendSuccess($data, 'Dashboard data retrieved successfully');
    }
} 