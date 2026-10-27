<?php

namespace Tests\Feature\Api;

use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductPriceControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Sanctum::actingAs(User::factory()->create());
    }

    public function test_returns_prices_for_product_ordered_by_id(): void
    {
        $product = Product::factory()->create();
        $firstCurrency = Currency::factory()->create();
        $secondCurrency = Currency::factory()->create();
        $firstPrice = ProductPrice::factory()
            ->for($product, 'product')
            ->create(['currency_id' => $firstCurrency->id]);
        $secondPrice = ProductPrice::factory()
            ->for($product, 'product')
            ->create(['currency_id' => $secondCurrency->id]);

        ProductPrice::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}/prices");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $firstPrice->id)
            ->assertJsonPath('data.1.id', $secondPrice->id)
            ->assertJsonPath('data.0.currency.id', $firstCurrency->id);
    }

    public function test_returns_empty_list_when_product_has_no_prices(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}/prices");

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_creates_price_for_product_and_returns_201(): void
    {
        $product = Product::factory()->create();
        $currency = Currency::factory()->create();

        $response = $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $currency->id,
            'price' => 250.75,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.currency_id', $currency->id)
            ->assertJsonPath('data.price', 250.75)
            ->assertJsonPath('data.currency.id', $currency->id);

        $this->assertDatabaseHas('product_prices', [
            'product_id' => $product->id,
            'currency_id' => $currency->id,
            'price' => 250.75,
        ]);
    }

    public function test_rejects_invalid_price_payload_with_422(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson("/api/products/{$product->id}/prices", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['currency_id', 'price'])
            ->assertJsonPath('errors.currency_id.0', 'The currency id field is required.')
            ->assertJsonPath('errors.price.0', 'The price field is required.');
    }

    public function test_rejects_non_existing_currency_with_422(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => 999,
            'price' => 10,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id')
            ->assertJsonPath('errors.currency_id.0', 'The selected currency id is invalid.');
    }

    public function test_rejects_negative_price_with_422(): void
    {
        $product = Product::factory()->create();
        $currency = Currency::factory()->create();

        $response = $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $currency->id,
            'price' => -1,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('price')
            ->assertJsonPath('errors.price.0', 'The price field must be at least 0.');
    }

    public function test_rejects_duplicate_price_for_same_product_and_currency_with_422(): void
    {
        $product = Product::factory()->create();
        $currency = Currency::factory()->create();
        ProductPrice::factory()
            ->for($product, 'product')
            ->create(['currency_id' => $currency->id]);

        $response = $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $currency->id,
            'price' => 99.99,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('currency_id')
            ->assertJsonPath('errors.currency_id.0', 'The product already has a price for this currency.');

        $this->assertDatabaseCount('product_prices', 1);
    }

    public function test_allows_same_currency_for_different_products(): void
    {
        $currency = Currency::factory()->create();
        $firstProduct = Product::factory()->create();
        $secondProduct = Product::factory()->create();
        ProductPrice::factory()
            ->for($firstProduct, 'product')
            ->create(['currency_id' => $currency->id]);

        $response = $this->postJson("/api/products/{$secondProduct->id}/prices", [
            'currency_id' => $currency->id,
            'price' => 20,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.product_id', $secondProduct->id)
            ->assertJsonPath('data.currency_id', $currency->id);
    }

    public function test_returns_404_when_product_does_not_exist(): void
    {
        $response = $this->getJson('/api/products/999/prices');

        $response->assertNotFound()
            ->assertJsonPath('message', 'Resource not found.');
    }

    public function test_returns_404_when_creating_price_for_missing_product(): void
    {
        $currency = Currency::factory()->create();

        $response = $this->postJson('/api/products/999/prices', [
            'currency_id' => $currency->id,
            'price' => 10,
        ]);

        $response->assertNotFound()
            ->assertJsonPath('message', 'Resource not found.');
    }

    public function test_deleting_product_also_deletes_its_prices(): void
    {
        $product = Product::factory()->create();
        $price = ProductPrice::factory()->for($product, 'product')->create();

        $this->deleteJson("/api/products/{$product->id}")->assertOk();

        $this->assertModelMissing($price);
    }

    public function test_requires_authentication(): void
    {
        $this->app['auth']->forgetGuards();

        $this->getJson('/api/products/1/prices')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }
}
