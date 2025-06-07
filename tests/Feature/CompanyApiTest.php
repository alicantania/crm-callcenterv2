<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function index_returns_all_companies()
    {
        Company::factory()->count(3)->create();

        $response = $this->getJson('/api/companies');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function calls_returns_only_that_company_calls()
    {
        $company = Company::factory()->create();
        Call::factory()->count(2)->create(['company_id' => $company->id]);
        $other = Company::factory()->create();
        Call::factory()->count(1)->create(['company_id' => $other->id]);

        $response = $this->getJson("/api/companies/{$company->id}/calls");

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonStructure([
                     '*' => ['id', 'company_id', 'user_id', 'status', 'call_date', 'notes'],
                 ]);
    }

    /** @test */
    public function index_returns_empty_array_when_no_companies()
    {
        $response = $this->getJson('/api/companies');

        $response->assertStatus(200)
                 ->assertExactJson([]);
    }

    /** @test */
    public function calls_returns_empty_array_when_no_calls()
    {
        $company = Company::factory()->create();
        $response = $this->getJson("/api/companies/{$company->id}/calls");

        $response->assertStatus(200)
                 ->assertExactJson([]);
    }

    /** @test */
    public function calls_invalid_company_returns_404()
    {
        $response = $this->getJson('/api/companies/999999/calls');

        $response->assertStatus(404);
    }

    /** @test */
    public function test_get_companies()
    {
        Company::factory(5)->create();

        $response = $this->getJson('/api/companies');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    /** @test */
    public function test_create_company()
    {
        $data = [
            'cif' => 'A12345678',
            'name' => 'Test Company',
            'status' => 'contactada'
        ];

        $response = $this->postJson('/api/companies', $data);

        $response->assertStatus(201)
            ->assertJsonFragment($data);
    }

    /** @test */
    public function test_update_company()
    {
        $company = Company::factory()->create();

        $response = $this->putJson("/api/companies/{$company->id}", [
            'status' => 'seguimiento'
        ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'seguimiento']);
    }

    /** @test */
    public function test_delete_company()
    {
        $company = Company::factory()->create();

        $response = $this->deleteJson("/api/companies/{$company->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
    }
}
