<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Property;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContractFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_owner_can_create_a_contract(): void
    {
        $owner = User::factory()->owner()->create();
        $tenant = User::factory()->tenant()->create();
        $prop = Property::factory()->create(['user_id' => $owner->id]);
        $tmpl = ContractTemplate::factory()->create();

        $contract = Contract::factory()->create([
            'contract_template_id' => $tmpl->id,
            'property_id' => $prop->id,
            'tenant_id' => $tenant->id,
        ]);

        $mock = Mockery::mock(ContractService::class);
        $mock->shouldReceive('createContract')->once()->andReturn($contract);
        $this->app->instance(ContractService::class, $mock);

        $this->actingAs($owner)
            ->postJson('/api/contracts', [
                'contract_template_id' => $tmpl->id,
                'property_id' => $prop->id,
                'tenant_id' => $tenant->id,
                'price' => 500,
                'deposit' => 500,
                'utilities_included' => false,
                'start_date' => '2025-01-01',
                'status' => 'draft',
                'token_values' => ['name' => 'Ana'],
            ])
            ->assertCreated()
            ->assertJsonPath('data.id', $contract->id);
    }

    public function test_owner_can_preview_contract_pdf(): void
    {
        $owner = User::factory()->owner()->create();
        $contract = Contract::factory()->create();

        $mock = Mockery::mock(ContractService::class);
        $mock->shouldReceive('getPreviewFile')
            ->with(Mockery::type(Contract::class))
            ->once()
            ->andReturn([
                'content' => 'dummy pdf bytes',
                'mime' => 'application/pdf',
                'name' => 'contract.pdf',
            ]);
        $this->app->instance(ContractService::class, $mock);

        $this->actingAs($owner)
            ->get("/api/contracts/{$contract->id}/preview")
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
