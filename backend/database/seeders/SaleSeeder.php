<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\Business;
use App\Models\Branch;
use App\Models\User;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\SaleItem;

class SaleSeeder extends Seeder
{
    public function run()
    {
        // Get or create necessary relationships
        $business = Business::first() ?? Business::factory()->create();
        $branch = Branch::first() ?? Branch::factory()->create(['business_id' => $business->id]);
        $cashier = User::where('role', 'cashier')->first() ?? User::factory()->create([
            'role' => 'cashier',
            'business_id' => $business->id,
            'branch_id' => $branch->id,
        ]);
        $customers = Customer::all();
        $inventory = Inventory::all();

        if ($inventory->isEmpty()) {
            $this->call(InventorySeeder::class);
            $inventory = Inventory::all();
        }

        // Create sample sales
        for ($i = 1; $i <= 5; $i++) {
            $sale = Sale::create([
                'sale_number' => 'SALE-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'total_amount' => 0, // Will be calculated from items
                'discount_amount' => 0,
                'tax_amount' => 0,
                'final_amount' => 0,
                'payment_method' => 'credit_card',
                'payment_status' => 'completed',
                'status' => 'completed',
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'cashier_id' => $cashier->id,
                'customer_id' => $customers->random()->id,
            ]);

            // Create 2-4 items for each sale
            $numItems = rand(2, 4);
            $totalAmount = 0;

            for ($j = 1; $j <= $numItems; $j++) {
                $item = $inventory->random();
                $quantity = rand(1, 3);
                $unitPrice = $item->unit_price;
                $discountAmount = rand(0, $unitPrice * 0.2);
                $taxAmount = ($unitPrice - $discountAmount) * 0.1;
                $totalAmount = ($unitPrice - $discountAmount + $taxAmount) * $quantity;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'inventory_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $discountAmount,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                ]);

                // Update sale totals
                $sale->total_amount += $totalAmount;
                $sale->discount_amount += $discountAmount;
                $sale->tax_amount += $taxAmount;
                $sale->final_amount = $sale->total_amount - $sale->discount_amount + $sale->tax_amount;
                $sale->save();
            }
        }

        // Create additional random sales using the factory
        Sale::factory()->count(5)->create([
            'business_id' => $business->id,
            'branch_id' => $branch->id,
            'cashier_id' => $cashier->id,
        ])->each(function ($sale) use ($inventory) {
            // Create 1-3 items for each random sale
            $numItems = rand(1, 3);
            for ($i = 0; $i < $numItems; $i++) {
                SaleItem::factory()->create([
                    'sale_id' => $sale->id,
                    'inventory_id' => $inventory->random()->id,
                ]);
            }
        });
    }
} 