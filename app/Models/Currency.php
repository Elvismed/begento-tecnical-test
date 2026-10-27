<?php

namespace App\Models;

use Database\Factories\CurrencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'symbol', 'exchange_rate'])]
class Currency extends Model
{
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<ProductPrice, $this>
     */
    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
        ];
    }
}
