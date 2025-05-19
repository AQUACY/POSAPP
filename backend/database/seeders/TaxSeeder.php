<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tax;
use App\Models\Business;

class TaxSeeder extends Seeder
{
    public function run()
    {
        // Get all businesses
        $businesses = Business::all();

        foreach ($businesses as $business) {
            // VAT (Value Added Tax)
            Tax::create([
                'business_id' => $business->id,
                'name' => 'VAT',
                'rate' => 15.00,
                'is_active' => true,
                'order' => 1
            ]);

            // NHIL (National Health Insurance Levy)
            Tax::create([
                'business_id' => $business->id,
                'name' => 'NHIL',
                'rate' => 2.50,
                'is_active' => true,
                'order' => 2
            ]);

            // GETFund (Ghana Education Trust Fund)
            Tax::create([
                'business_id' => $business->id,
                'name' => 'GETFund',
                'rate' => 2.50,
                'is_active' => true,
                'order' => 3
            ]);

            // COVID-19 Health Recovery Levy
            Tax::create([
                'business_id' => $business->id,
                'name' => 'COVID-19 Levy',
                'rate' => 1.00,
                'is_active' => true,
                'order' => 4
            ]);
        }
    }
} 