<?php

namespace App\Services\SuperAdmin;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Service class for handling business-related operations
 * Implements business logic that can be reused across controllers
 */
class BusinessService
{
    /**
     * Create a new business
     *
     * @param array $data
     * @return Business
     */
    public function createBusiness(array $data): Business
    {
        return DB::transaction(function () use ($data) {
            $business = Business::create($data);
            
            // Create default receipt settings if not provided
            if (!isset($data['receipt_settings'])) {
                $business->update([
                    'receipt_settings' => $this->getDefaultReceiptSettings()
                ]);
            }

            // Create default report settings if not provided
            if (!isset($data['report_settings'])) {
                $business->update([
                    'report_settings' => $this->getDefaultReportSettings()
                ]);
            }

            return $business;
        });
    }

    /**
     * Update business settings
     *
     * @param Business $business
     * @param array $data
     * @return Business
     */
    public function updateBusiness(Business $business, array $data): Business
    {
        return DB::transaction(function () use ($business, $data) {
            $business->update($data);
            return $business;
        });
    }

    /**
     * Upload business logo
     *
     * @param Business $business
     * @param mixed $file
     * @return string
     */
    public function uploadLogo(Business $business, $file): string
    {
        $path = $file->store('business-logos', 'public');
        
        // Delete old logo if exists
        if ($business->logo) {
            Storage::disk('public')->delete($business->logo);
        }

        return $path;
    }

    /**
     * Get default receipt settings
     *
     * @return array
     */
    private function getDefaultReceiptSettings(): array
    {
        return [
            'header' => 'Thank you for shopping with us!',
            'footer' => 'Please come again!',
            'show_tax' => true,
            'show_logo' => true,
            'show_business_info' => true,
            'show_branch_info' => true,
            'show_cashier_info' => true,
            'show_date_time' => true
        ];
    }

    /**
     * Get default report settings
     *
     * @return array
     */
    private function getDefaultReportSettings(): array
    {
        return [
            'currency' => 'USD',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
            'show_logo' => true,
            'show_business_info' => true,
            'show_branch_info' => true,
            'show_tax_summary' => true,
            'show_payment_summary' => true
        ];
    }
} 