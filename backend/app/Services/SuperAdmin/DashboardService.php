<?php

namespace App\Services\SuperAdmin;

use App\Models\Business;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service class for handling dashboard analytics
 */
class DashboardService
{
    /**
     * Get overall platform statistics
     *
     * @return array
     */
    public function getPlatformStats(): array
    {
        return [
            'total_businesses' => Business::count(),
            'active_businesses' => Business::where('is_active', true)->count(),
            'total_branches' => Branch::count(),
            'total_users' => User::count(),
            'active_users' => User::whereHas('business', function ($query) {
                $query->where('is_active', true);
            })->count(),
        ];
    }

    /**
     * Get business growth statistics
     *
     * @param int $months
     * @return array
     */
    public function getBusinessGrowth(int $months = 12): array
    {
        $startDate = Carbon::now()->subMonths($months);
        
        return Business::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->toArray();
    }

    /**
     * Get user growth statistics
     *
     * @param int $months
     * @return array
     */
    public function getUserGrowth(int $months = 12): array
    {
        $startDate = Carbon::now()->subMonths($months);
        
        return User::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->toArray();
    }

    /**
     * Get top performing businesses
     *
     * @param int $limit
     * @return array
     */
    public function getTopBusinesses(int $limit = 5): array
    {
        return Business::withCount(['branches', 'users'])
            ->orderBy('branches_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get recent activities
     *
     * @param int $limit
     * @return array
     */
    public function getRecentActivities(int $limit = 10): array
    {
        // This would typically come from an activity log table
        // For now, we'll return a placeholder
        return [
            'type' => 'activity_log',
            'data' => []
        ];
    }
} 