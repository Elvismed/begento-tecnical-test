<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->currencyCode(),
            'symbol' => fake()->randomElement(['$', '€', '£', '¥']),
            'exchange_rate' => fake()->randomFloat(6, 0.5, 2),
        ];
    }
}
