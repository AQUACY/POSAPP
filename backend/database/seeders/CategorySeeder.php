<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Business;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Get or create a business for the categories
        $business = Business::first() ?? Business::factory()->create();

        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'business_id' => $business->id,
            ],
            [
                'name' => 'Clothing',
                'description' => 'Apparel and fashion items',
                'business_id' => $business->id,
            ],
            [
                'name' => 'Food & Beverages',
                'description' => 'Food items and drinks',
                'business_id' => $business->id,
            ],
            [
                'name' => 'Home & Kitchen',
                'description' => 'Home appliances and kitchenware',
                'business_id' => $business->id,
            ],
            [
                'name' => 'Office Supplies',
                'description' => 'Office equipment and stationery',
                'business_id' => $business->id,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create additional random categories using the factory
        Category::factory()->count(5)->create([
            'business_id' => $business->id,
        ]);
    }
} 