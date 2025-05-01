<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Branch;
use App\Models\Business;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company() . ' Branch',
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'settings' => json_encode([
                'opening_time' => $this->faker->time('H:i:s'),
                'closing_time' => $this->faker->time('H:i:s'),
                'manager' => [
                    'name' => $this->faker->name(),
                    'phone' => $this->faker->phoneNumber(),
                    'email' => $this->faker->email(),
                ]
            ]),
            'is_active' => $this->faker->boolean(),
            'business_id' => Business::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 