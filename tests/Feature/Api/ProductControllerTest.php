<?php

namespace Tests\Feature\Api;

use App\Models\Currency;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_returns_products_list_ordered_by_id(): void
    {
        $currency = Currency::factory()->create();
        $firstProduct = Product::factory()->for($currency)->create();
        $secondProduct = Product::factory()->for($currency)->create();

        $response = $this->getJson('/api/products');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $firstProduct->id)
            ->assertJsonPath('data.1.id', $secondProduct->id)
            ->assertJsonPath('data.0.currency.id', $currency->id);
    }

    public function test_creates_product_and_returns_201(): void
    {
        $currency = Currency::factory()->create();

        $response = $this->postJson('/api/products', [
            'name' => 'Keyboard',
            'description' => 'Mechanical keyboard',
            'price' => 99.99,
            'currency_id' => $currency->id,
            'tax_cost' => 16.00,
            'manufacturing_cost' => 40.00,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Keyboard')
            ->assertJsonPath('data.price', 99.99)
            ->assertJsonPath('data.currency_id', $currency->id)
            ->assertJsonPath('data.currency.id', $currency->id);

        $this->assertDatabaseHas('products', [
            'name' => 'Keyboard',
            'currency_id' => $currency->id,
        ]);
    }

    public function test_rejects_invalid_product_payload_with_422(): void
    {
        $response = $this->postJson('/api/products', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'description',
                'price',
                'currency_id',
                'tax_cost',
                'manufacturing_cost',
            ])
            ->assertJsonPath('errors.name.0', 'The name field is required.');
    }

    public function test_rejects_non_existing_currency_with_422(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'Keyboard',
            'description' => 'Mechanical keyboard',
            'price' => 99.99,
            'currency_id' => 999,
            'tax_cost' => 16.00,
            'manufacturing_cost' => 40.00,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id')
            ->assertJsonPath('errors.currency_id.0', 'The selected currency id is invalid.');
    }

    public function test_returns_product_by_id(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', $product->name)
            ->assertJsonPath('data.currency.id', $product->currency_id);
    }

    public function test_returns_404_for_missing_product(): void
    {
        $response = $this->getJson('/api/products/999');

        $response->assertNotFound()
            ->assertJsonPath('message', 'Resource not found.');
    }

    public function test_unknown_api_endpoint_returns_json_404(): void
    {
        $response = $this->getJson('/api/unknown');

        $response->assertNotFound()
            ->assertJsonPath('message', 'Endpoint not found.');
    }

    public function test_updates_product_and_returns_200(): void
    {
        $product = Product::factory()->create();
        $newCurrency = Currency::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated keyboard',
            'price' => 150.50,
            'currency_id' => $newCurrency->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated keyboard')
            ->assertJsonPath('data.price', 150.5)
            ->assertJsonPath('data.currency_id', $newCurrency->id);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated keyboard',
            'currency_id' => $newCurrency->id,
        ]);
    }

    public function test_rejects_invalid_update_payload_with_422(): void
    {
        $product = Product::factory()->create();

        $response = $this->putJson("/api/products/{$product->id}", [
            'price' => 'free',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('price')
            ->assertJsonPath('errors.price.0', 'The price field must be a number.');
    }

    public function test_deletes_product_and_returns_200(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('message', 'Product deleted successfully.');

        $this->assertModelMissing($product);
    }
}
