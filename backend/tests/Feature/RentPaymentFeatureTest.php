<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Models\Contract;
use App\Models\Property;
use App\Models\RentPayment;
use App\Models\User;
use App\Services\RentPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RentPaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_rent_payment(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $tenant = User::factory()->tenant()->create();
        $contract = Contract::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        $payload = [
            'contract_id' => $contract->id,
            'period_start' => '2025-05-01',
            'period_end' => '2025-05-31',
            'due_date' => '2025-06-05',
            'amount' => 750,
        ];

        $this->actingAs($owner)
            ->postJson('/api/rent-payments', $payload)
            ->assertCreated()
            ->assertJsonPath('data.amount', '750.00')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('rent_payments', [
            'contract_id' => $contract->id,
            'amount' => 750,
        ]);
    }

    public function test_tenant_can_mark_rent_payment_paid(): void
    {
        $tenant  = User::factory()->tenant()->create();
        $rentPay = RentPayment::factory()->create(['status' => 'pending', 'amount' => 500]);
        $rentPay->contract()->update(['tenant_id' => $tenant->id]);

        // evitamos que el servicio cree registros en payments
        $mock = Mockery::mock(RentPaymentService::class);
        $mock->shouldReceive('markPaid')
            ->once()
            ->andReturn(tap($rentPay)->update(['status' => 'paid']));
        $this->app->instance(RentPaymentService::class, $mock);

        $this->actingAs($tenant)
            ->putJson("/api/rent-payments/{$rentPay->id}", ['status' => 'paid'])
            ->assertCreated()
            ->assertJsonPath('data.status', 'paid');

        $this->assertDatabaseHas('rent_payments', [
            'id'     => $rentPay->id,
            'status' => 'paid',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
