<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Company;
use App\Models\Call;

class CompanyApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function index_returns_all_companies()
    {
        Company::factory()->count(3)->create();

        $response = $this->getJson('/api/companies');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
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
}
