<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SaleItemFactory extends Factory
{
    public function definition()
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $unitPrice = $this->faker->randomFloat(2, 1, 100);
        $discountAmount = $this->faker->randomFloat(2, 0, $unitPrice * 0.2);
        $taxAmount = ($unitPrice - $discountAmount) * 0.1;
        $totalAmount = ($unitPrice - $discountAmount + $taxAmount) * $quantity;

        return [
            'sale_id' => \App\Models\Sale::factory(),
            'inventory_id' => \App\Models\Inventory::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ];
    }
} 