<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SaleApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_create_sale()
    {
        $company = Company::factory()->create();
        $product = Product::factory()->create();

        $data = [
            'company_id' => $company->id,
            'product_id' => $product->id,
            'amount' => 1000,
            'status' => 'pendiente',
            'sale_date' => now()->format('Y-m-d')
        ];

        $response = $this->postJson('/api/sales', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);
    }

    public function test_update_sale_status()
    {
        $sale = Sale::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/sales/{$sale->id}", [
            'status' => 'completada'
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'completada']);
    }

    public function test_delete_sale()
    {
        $sale = Sale::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/sales/{$sale->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('sales', ['id' => $sale->id]);
    }
}
