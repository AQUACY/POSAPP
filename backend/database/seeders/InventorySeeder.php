<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inventory;
use App\Models\Category;
use App\Models\Business;
use App\Models\Branch;

class InventorySeeder extends Seeder
{
    public function run()
    {
        // Get or create necessary relationships
        $business = Business::first() ?? Business::factory()->create();
        $branch = Branch::first() ?? Branch::factory()->create(['business_id' => $business->id]);
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        $inventoryItems = [
            [
                'name' => 'Laptop Pro',
                'description' => 'High-performance laptop with 16GB RAM',
                'sku' => 'ELEC-LP-001',
                'barcode' => '123456789012',
                'quantity' => 10,
                'unit_price' => 999.99,
                'cost_price' => 700.00,
                'reorder_level' => 5,
                'category_id' => $categories->where('name', 'Electronics')->first()->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
            ],
            [
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse',
                'sku' => 'ELEC-MO-001',
                'barcode' => '123456789013',
                'quantity' => 50,
                'unit_price' => 29.99,
                'cost_price' => 15.00,
                'reorder_level' => 10,
                'category_id' => $categories->where('name', 'Electronics')->first()->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
            ],
            [
                'name' => 'Office Chair',
                'description' => 'Ergonomic office chair',
                'sku' => 'OFF-CH-001',
                'barcode' => '123456789014',
                'quantity' => 15,
                'unit_price' => 199.99,
                'cost_price' => 120.00,
                'reorder_level' => 3,
                'category_id' => $categories->where('name', 'Office Supplies')->first()->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
            ],
            [
                'name' => 'Coffee Maker',
                'description' => 'Automatic coffee maker',
                'sku' => 'HOME-CM-001',
                'barcode' => '123456789015',
                'quantity' => 20,
                'unit_price' => 79.99,
                'cost_price' => 45.00,
                'reorder_level' => 5,
                'category_id' => $categories->where('name', 'Home & Kitchen')->first()->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
            ],
            [
                'name' => 'T-Shirt',
                'description' => 'Cotton t-shirt',
                'sku' => 'CLOTH-TS-001',
                'barcode' => '123456789016',
                'quantity' => 100,
                'unit_price' => 19.99,
                'cost_price' => 8.00,
                'reorder_level' => 20,
                'category_id' => $categories->where('name', 'Clothing')->first()->id,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
            ],
        ];

        foreach ($inventoryItems as $item) {
            Inventory::create($item);
        }

        // Create additional random inventory items using the factory
        Inventory::factory()->count(10)->create([
            'business_id' => $business->id,
            'branch_id' => $branch->id,
        ]);
    }
} 