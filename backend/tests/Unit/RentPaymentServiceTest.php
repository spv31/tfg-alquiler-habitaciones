<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Models\Property;
use App\Models\RentPayment;
use App\Models\User;
use App\Services\RentPaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\StripeClient;
use Tests\TestCase;

class RentPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private RentPaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => 'owner']));

        $this->app->instance(StripeClient::class, Mockery::mock(StripeClient::class)->shouldIgnoreMissing());
        $this->app->instance(\App\Services\UserService::class, Mockery::mock(\App\Services\UserService::class)->shouldIgnoreMissing());

        $this->service = app(RentPaymentService::class);
    }

    public function test_create_rent_payment_for_active_contract(): void
    {
        $property = Property::factory()->create(['user_id' => auth()->id()]);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $contract = Contract::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        $rp = $this->service->create([
            'contract_id' => $contract->id,
            'period_start' => '2025-01-01',
            'period_end' => '2025-01-31',
            'due_date' => '2025-02-05',
            'amount' => 800,
        ]);

        $this->assertEquals('pending', $rp->status);
        $this->assertEquals($tenant->id, $rp->contract->tenant->id);
    }

    public function test_mark_paid_creates_payment_and_changes_status(): void
    {
        $rp = RentPayment::factory()->create(['status' => 'pending']);

        $rp = $this->service->markPaid($rp, ['method' => 'manual_transfer']);

        $this->assertEquals('paid', $rp->status);
        $this->assertCount(1, $rp->payments);
    }

    public function test_breakdown_for_owner(): void
    {
        $property = Property::factory()->create(['user_id' => auth()->id()]);
        $tenant = User::factory()->create();

        $contract = Contract::factory()->create([
            'property_id' => $property->id,
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);

        RentPayment::factory()->create([
            'contract_id' => $contract->id,
            'amount' => 600,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        RentPayment::factory()->create([
            'contract_id' => $contract->id,
            'amount' => 400,
            'status' => 'pending',
        ]);

        $tot = $this->service->breakdownForOwner(now()->subMonth(), now()->addMonth());

        $this->assertEquals(['paid' => 600, 'pending' => 400], $tot);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
