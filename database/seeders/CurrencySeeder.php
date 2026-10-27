<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Seed the application's base currencies.
     */
    public function run(): void
    {
        $currencies = [
            ['name' => 'USD', 'symbol' => '$', 'exchange_rate' => 1.000000],
            ['name' => 'EUR', 'symbol' => '€', 'exchange_rate' => 0.920000],
            ['name' => 'MXN', 'symbol' => '$', 'exchange_rate' => 17.500000],
            ['name' => 'COP', 'symbol' => '$', 'exchange_rate' => 4000.000000],
        ];

        foreach ($currencies as $currency) {
            Currency::query()->updateOrCreate(
                ['name' => $currency['name']],
                $currency,
            );
        }
    }
}
