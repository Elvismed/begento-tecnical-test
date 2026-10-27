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
            ['name' => 'GBP', 'symbol' => '£', 'exchange_rate' => 0.790000],
            ['name' => 'JPY', 'symbol' => '¥', 'exchange_rate' => 150.000000],
            ['name' => 'CNY', 'symbol' => '¥', 'exchange_rate' => 7.250000],
            ['name' => 'CHF', 'symbol' => 'CHF', 'exchange_rate' => 0.880000],
            ['name' => 'CAD', 'symbol' => 'C$', 'exchange_rate' => 1.370000],
            ['name' => 'AUD', 'symbol' => 'A$', 'exchange_rate' => 1.520000],
            ['name' => 'BRL', 'symbol' => 'R$', 'exchange_rate' => 5.400000],
            ['name' => 'MXN', 'symbol' => '$', 'exchange_rate' => 17.500000],
            ['name' => 'COP', 'symbol' => '$', 'exchange_rate' => 4000.000000],
            ['name' => 'ARS', 'symbol' => '$', 'exchange_rate' => 1000.000000],
            ['name' => 'CLP', 'symbol' => '$', 'exchange_rate' => 950.000000],
            ['name' => 'PEN', 'symbol' => 'S/', 'exchange_rate' => 3.750000],
            ['name' => 'INR', 'symbol' => '₹', 'exchange_rate' => 83.500000],
        ];

        foreach ($currencies as $currency) {
            Currency::query()->updateOrCreate(
                ['name' => $currency['name']],
                $currency,
            );
        }
    }
}
