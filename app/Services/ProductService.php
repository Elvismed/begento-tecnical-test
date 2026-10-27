<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    /**
     * @return Collection<int, Product>
     */
    public function list(): Collection
    {
        return Product::query()
            ->with('currency')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $product = Product::query()->create($data);

        return $product->load('currency');
    }

    public function find(Product $product): Product
    {
        return $product->load('currency');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->load('currency');
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * @return Collection<int, ProductPrice>
     */
    public function listPrices(Product $product): Collection
    {
        return $product->prices()
            ->with('currency')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createPrice(Product $product, array $data): ProductPrice
    {
        $price = $product->prices()->create($data);

        return $price->load('currency');
    }
}
