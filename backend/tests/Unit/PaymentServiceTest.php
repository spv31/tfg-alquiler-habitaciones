<?php

namespace Tests\Unit;

use App\Models\BillShare;
use App\Models\Payment;
use App\Models\Property;
use App\Models\User;
use App\Models\UtilityBill;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\StripeClient;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $service;
    private StripeClient $stripeMock;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = User::factory()->create(['role' => 'owner']);
        $this->actingAs($owner);

        $this->stripeMock = Mockery::mock(StripeClient::class);
        $this->stripeMock->paymentIntents = Mockery::mock();
        $this->app->instance(StripeClient::class, $this->stripeMock);

        $this->service = app(PaymentService::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_mark_manual_sets_paid_and_updates_billshare(): void
    {
        $property = Property::factory()->create(['user_id' => auth()->id()]);
        $bill = UtilityBill::factory()->create([
            'property_id' => $property->id,
            'status' => 'split',
            'owner_id' => auth()->id(),
        ]);

        $share = BillShare::factory()->create([
            'utility_bill_id' => $bill->id,
            'status' => 'pending',
        ]);

        $payment = Payment::factory()->create(['bill_share_id' => $share->id]);

        $payment = $this->service->markManual($payment);

        $this->assertEquals('manual_transfer', $payment->method);
        $this->assertEquals('paid', $share->refresh()->status);
        $this->assertNotNull($payment->paid_at);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_sync_with_stripe_updates_when_succeeded(): void
    {
        $payment = Payment::factory()->create([
            'paid_at' => now(),
            'stripe_payment_intent_id' => 'pi_123',
        ]);

        // simular intent "succeeded"
        $this->stripeMock->paymentIntents
            ->shouldReceive('retrieve')
            ->once()
            ->with('pi_123')
            ->andReturn((object)['status' => 'succeeded']);

        $this->service->syncWithStripe('pi_123');

        $this->assertNotNull($payment->refresh()->paid_at);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
