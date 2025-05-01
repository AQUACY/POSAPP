<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Business;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        // Get or create a business for the customers
        $business = Business::first() ?? Business::factory()->create();

        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'address' => '123 Main St, Anytown, USA',
                'business_id' => $business->id,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1987654321',
                'address' => '456 Oak Ave, Somewhere, USA',
                'business_id' => $business->id,
            ],
            [
                'name' => 'Robert Johnson',
                'email' => 'robert.j@example.com',
                'phone' => '+1122334455',
                'address' => '789 Pine Rd, Elsewhere, USA',
                'business_id' => $business->id,
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.d@example.com',
                'phone' => '+1555666777',
                'address' => '321 Elm St, Nowhere, USA',
                'business_id' => $business->id,
            ],
            [
                'name' => 'Michael Wilson',
                'email' => 'michael.w@example.com',
                'phone' => '+1444333222',
                'address' => '654 Maple Dr, Anywhere, USA',
                'business_id' => $business->id,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        // Create additional random customers using the factory
        Customer::factory()->count(10)->create([
            'business_id' => $business->id,
        ]);
    }
} 