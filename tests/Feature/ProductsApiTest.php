<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductsApiTest extends TestCase
{
    use RefreshDatabase;

    private Currency $usd;
    private Currency $crc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usd = Currency::create([
            'name' => 'USD', 'symbol' => '$', 'exchange_rate' => 1,
        ]);
        $this->crc = Currency::create([
            'name' => 'CRC', 'symbol' => '₡', 'exchange_rate' => 500,
        ]);
    }

    private function payload(): array
    {
        return [
            'name' => 'Mouse inalámbrico',
            'description' => 'Mouse ergonómico para oficina',
            'price' => 25.50,
            'currency_id' => $this->usd->id,
            'tax_cost' => 3.32,
            'manufacturing_cost' => 10.00,
        ];
    }

    private function signIn(): void
    {
        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'password_segura_123',
        ]);
        Sanctum::actingAs($user);
    }

    public function test_health_check_returns_json(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_writing_requires_a_token(): void
    {
        $this->postJson('/api/products', $this->payload())
            ->assertUnauthorized();
    }

    public function test_can_create_read_update_and_delete_a_product(): void
    {
        $this->signIn();

        $created = $this->postJson('/api/products', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Mouse inalámbrico');
        $id = $created->json('data.id');

        $this->getJson("/api/products/{$id}")
            ->assertOk()
            ->assertJsonPath('data.currency.name', 'USD');

        $this->putJson("/api/products/{$id}", ['name' => 'Mouse actualizado'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Mouse actualizado');

        $this->getJson('/api/products')->assertOk()->assertJsonCount(1, 'data');

        $this->deleteJson("/api/products/{$id}")
            ->assertOk()
            ->assertJsonPath('message', 'Producto eliminado correctamente');

        $this->getJson("/api/products/{$id}")->assertNotFound();
    }

    public function test_can_store_prices_only_once_for_each_extra_currency(): void
    {
        $this->signIn();
        $product = Product::create($this->payload());

        $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $this->crc->id, 'price' => 12750,
        ])->assertCreated();

        $this->getJson("/api/products/{$product->id}/prices")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $this->crc->id, 'price' => 13000,
        ])->assertUnprocessable();

        $this->postJson("/api/products/{$product->id}/prices", [
            'currency_id' => $this->usd->id, 'price' => 25.50,
        ])->assertUnprocessable();
    }

    public function test_invalid_fields_return_a_validation_error(): void
    {
        $this->signIn();
        $data = $this->payload();
        $data['price'] = -5;
        $data['currency_id'] = 99999;

        $this->postJson('/api/products', $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['price', 'currency_id']);
    }

    public function test_deleting_a_product_removes_its_extra_prices(): void
    {
        $this->signIn();
        $product = Product::create($this->payload());
        $product->prices()->create(['currency_id' => $this->crc->id, 'price' => 12500]);

        $this->deleteJson("/api/products/{$product->id}")->assertOk();
        $this->assertDatabaseCount('product_prices', 0);
    }
}
