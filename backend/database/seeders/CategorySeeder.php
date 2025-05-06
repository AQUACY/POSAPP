<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Business;
use App\Models\Branch;

class CategorySeeder extends Seeder
{
    public function run()
    {
        // Get all businesses
        $businesses = Business::all();

        foreach ($businesses as $business) {
            $categories = [
                [
                    'name' => 'Groceries',
                    'description' => 'Food items, snacks, and daily essentials',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Beverages',
                    'description' => 'Soft drinks, water, juices, and alcoholic beverages',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Fresh Produce',
                    'description' => 'Fresh fruits, vegetables, and herbs',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Dairy & Eggs',
                    'description' => 'Milk, cheese, yogurt, and eggs',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Meat & Seafood',
                    'description' => 'Fresh meat, poultry, fish, and seafood',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Bakery',
                    'description' => 'Bread, pastries, and baked goods',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Personal Care',
                    'description' => 'Toiletries, hygiene products, and cosmetics',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Household',
                    'description' => 'Cleaning supplies and household essentials',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Frozen Foods',
                    'description' => 'Frozen meals, ice cream, and frozen vegetables',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Snacks & Confectionery',
                    'description' => 'Chips, chocolates, candies, and other snacks',
                    'business_id' => $business->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            // Create categories for each business
            foreach ($categories as $category) {
                Category::firstOrCreate(
                    [
                        'name' => $category['name'],
                        'business_id' => $business->id
                    ],
                    $category
                );
            }

            // Get all branches for this business
            $branches = Branch::where('business_id', $business->id)->get();

            // Ensure each branch has access to these categories
            foreach ($branches as $branch) {
                foreach (Category::where('business_id', $business->id)->get() as $category) {
                    // Here you could create branch-category relationships if needed
                    // Or any other branch-specific category setup
                }
            }
        }
    }
} 