<?php

namespace App\Services\SuperAdmin;

use App\Models\Business;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service for handling super admin reports
 */
class ReportService
{
    /**
     * Get sales report across all businesses
     *
     * @param array $filters
     * @return array
     */
    public function getSalesReport(array $filters = []): array
    {
        $query = Sale::query()
            ->select(
                'businesses.name as business_name',
                DB::raw('COUNT(sales.id) as total_sales'),
                DB::raw('SUM(sales.total_amount) as total_revenue'),
                DB::raw('AVG(sales.total_amount) as average_sale')
            )
            ->join('businesses', 'sales.business_id', '=', 'businesses.id')
            ->groupBy('businesses.id', 'businesses.name');

        if (isset($filters['start_date'])) {
            $query->where('sales.created_at', '>=', Carbon::parse($filters['start_date']));
        }

        if (isset($filters['end_date'])) {
            $query->where('sales.created_at', '<=', Carbon::parse($filters['end_date']));
        }

        return $query->get()->toArray();
    }

    /**
     * Get revenue report across all businesses
     *
     * @param array $filters
     * @return array
     */
    public function getRevenueReport(array $filters = []): array
    {
        $query = Sale::query()
            ->select(
                'businesses.name as business_name',
                DB::raw('DATE(sales.created_at) as date'),
                DB::raw('SUM(sales.total_amount) as daily_revenue')
            )
            ->join('businesses', 'sales.business_id', '=', 'businesses.id')
            ->groupBy('businesses.id', 'businesses.name', 'date');

        if (isset($filters['start_date'])) {
            $query->where('sales.created_at', '>=', Carbon::parse($filters['start_date']));
        }

        if (isset($filters['end_date'])) {
            $query->where('sales.created_at', '<=', Carbon::parse($filters['end_date']));
        }

        return $query->get()->toArray();
    }

    /**
     * Get business performance report
     *
     * @param array $filters
     * @return array
     */
    public function getBusinessReport(array $filters = []): array
    {
        $query = Business::query()
            ->select(
                'businesses.*',
                DB::raw('COUNT(DISTINCT branches.id) as total_branches'),
                DB::raw('COUNT(DISTINCT users.id) as total_users'),
                DB::raw('COUNT(DISTINCT sales.id) as total_sales'),
                DB::raw('SUM(sales.total_amount) as total_revenue')
            )
            ->leftJoin('branches', 'businesses.id', '=', 'branches.business_id')
            ->leftJoin('users', 'businesses.id', '=', 'users.business_id')
            ->leftJoin('sales', 'businesses.id', '=', 'sales.business_id')
            ->groupBy('businesses.id');

        if (isset($filters['status'])) {
            $query->where('businesses.is_active', $filters['status'] === 'active');
        }

        return $query->get()->toArray();
    }
} 