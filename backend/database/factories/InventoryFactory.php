<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'sku' => $this->faker->unique()->bothify('SKU-####-????'),
            'barcode' => $this->faker->unique()->ean13,
            'quantity' => $this->faker->numberBetween(0, 1000),
            'unit_price' => $this->faker->randomFloat(2, 1, 1000),
            'cost_price' => $this->faker->randomFloat(2, 0.5, 500),
            'reorder_level' => $this->faker->numberBetween(5, 50),
            'category_id' => \App\Models\Category::factory(),
            'business_id' => \App\Models\Business::factory(),
            'branch_id' => \App\Models\Branch::factory(),
        ];
    }
} 