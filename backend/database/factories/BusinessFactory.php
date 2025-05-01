<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Business;

class BusinessFactory extends Factory
{
    protected $model = Business::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'logo' => $this->faker->imageUrl(200, 200, 'business'),
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'tax_id' => $this->faker->unique()->numerify('##########'),
            'receipt_settings' => json_encode([
                'header' => $this->faker->company(),
                'footer' => 'Thank you for your business!',
                'show_tax' => true,
                'show_discount' => true,
                'show_qr_code' => true,
            ]),
            'report_settings' => json_encode([
                'currency' => 'USD',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i:s',
                'show_logo' => true,
            ]),
            'settings' => json_encode([
                'timezone' => $this->faker->timezone(),
                'language' => 'en',
                'currency' => 'USD',
                'tax_rate' => 10,
                'decimal_places' => 2,
            ]),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 