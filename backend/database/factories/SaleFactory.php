<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    public function definition()
    {
        $totalAmount = $this->faker->randomFloat(2, 10, 1000);
        $discountAmount = $this->faker->randomFloat(2, 0, $totalAmount * 0.2);
        $taxAmount = ($totalAmount - $discountAmount) * 0.1;
        $finalAmount = $totalAmount - $discountAmount + $taxAmount;

        return [
            'sale_number' => $this->faker->unique()->bothify('SALE-####-????'),
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'final_amount' => $finalAmount,
            'payment_method' => $this->faker->randomElement(['cash', 'credit_card', 'debit_card']),
            'payment_status' => $this->faker->randomElement(['pending', 'completed', 'failed']),
            'status' => $this->faker->randomElement(['pending', 'completed', 'cancelled']),
            'business_id' => \App\Models\Business::factory(),
            'branch_id' => \App\Models\Branch::factory(),
            'cashier_id' => \App\Models\User::factory(),
            'customer_id' => \App\Models\Customer::factory(),
        ];
    }
} 