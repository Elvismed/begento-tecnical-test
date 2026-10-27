<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

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
        $product = DB::transaction(function () use ($data): Product {
            $product = Product::query()->create($data);

            $this->syncBasePrice($product);

            return $product;
        });

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
        DB::transaction(function () use ($product, $data): void {
            $product->update($data);

            $this->syncBasePrice($product);
        });

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

    /**
     * Keep the product's own currency price aligned with its base price.
     */
    private function syncBasePrice(Product $product): void
    {
        $product->prices()->updateOrCreate(
            ['currency_id' => $product->currency_id],
            ['price' => $product->price],
        );
    }
}
