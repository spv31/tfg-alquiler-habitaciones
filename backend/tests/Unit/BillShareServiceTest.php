<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\BillShareService;
use App\Services\UserService;
use App\Models\UtilityBill;
use App\Models\Property;
use App\Models\User;
use App\Models\BillShare;
use App\Models\Contract;
use App\Models\Room;
use Exception;
use Mockery;
use Stripe\StripeClient;

class BillShareServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BillShareService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $stripeMock = Mockery::mock(StripeClient::class)->shouldIgnoreMissing();
        $this->service = new BillShareService($stripeMock);

        $this->actingAs(User::factory()->create(['role' => 'owner']));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function create_throws_if_amount_exceeds_remaining(): void
    {
        $prop = Property::factory()->create();
        $bill = UtilityBill::factory()->create([
            'property_id' => $prop->id,
            'owner_id' => auth()->id(),
            'total_amount' => 50,
            'status' => 'pending'
        ]);

        $this->expectException(Exception::class);

        $this->service->create($bill, [
            'tenant_id' => User::factory()->create()->id,
            'amount' => 60,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function list_by_bill_returns_only_owner_shares(): void
    {
        $prop = Property::factory()->create();
        $bill = UtilityBill::factory()->create([
            'property_id' => $prop->id,
            'owner_id' => auth()->id(),
            'total_amount' => 100,
        ]);

        BillShare::factory()->count(2)->create(['utility_bill_id' => $bill->id]);

        $res = $this->service->listByBill($bill);

        $this->assertCount(2, $res);
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function test_auto_split_per_room_assigns_correct_amount(): void
    {
        // Propiedad por habitaciones (4 en total, 3 alquiladas)
        $property = Property::factory()->create([
            'user_id' => auth()->id(),
            'total_rooms' => 4,
            'rental_type' => 'per_room',
        ]);

        $rooms   = Room::factory()->count(4)->create(['property_id' => $property->id]);
        $tenants = User::factory()->count(3)->create(['role' => 'tenant']);

        foreach (range(0, 2) as $i) {
            Contract::factory()->create([
                'property_id' => $property->id,
                'room_id' => $rooms[$i]->id,
                'tenant_id' => $tenants[$i]->id,
                'status' => 'active',
                'utilities_included' => false,
                'utilities_payer' => 'tenant',
            ]);
        }

        // Factura de 200 €
        $bill = UtilityBill::factory()->create([
            'property_id' => $property->id,
            'owner_id' => auth()->id(),
            'total_amount' => 200,
        ]);

        $shares = $this->service->autoSplit($bill);

        // 3 shares de 50 € (200 / 4 habitaciones)
        $this->assertCount(3, $shares);
        $this->assertTrue($shares->pluck('amount')->every(fn($a) => (float) $a === 50.0));
        $this->assertSame('pending', $bill->refresh()->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_auto_split_shared_pays_respect_owner_percentages(): void
    {
        // Propiedad de 4 habitaciones (3 alquiladas)
        $property = Property::factory()->create([
            'user_id' => auth()->id(),
            'total_rooms' => 4,
            'rental_type' => 'per_room',
        ]);

        $rooms = Room::factory()->count(4)->create(['property_id' => $property->id]);
        $tenants = User::factory()->count(3)->create(['role' => 'tenant']);

        // % del gasto que paga el propietario
        $ownerPercents = [20, 40, 50];  // 20 %, 40 %, 50 %
        foreach (range(0, 2) as $i) {
            Contract::factory()->create([
                'property_id' => $property->id,
                'room_id' => $rooms[$i]->id,
                'tenant_id' => $tenants[$i]->id,
                'status' => 'active',
                'utilities_included' => false,
                'utilities_payer' => 'shared',
                'utilities_proportion' => $ownerPercents[$i],
            ]);
        }

        // total 200 €
        $bill = UtilityBill::factory()->create([
            'property_id' => $property->id,
            'owner_id' => auth()->id(),
            'total_amount' => 200,
        ]);

        $shares = $this->service->autoSplit($bill)->sortBy('tenant_id')->values();

        $base = 200 / 4;
        $expected = collect($ownerPercents)
            ->map(fn($pct) => $base * (1 - $pct / 100)) // parte que paga inquilino
            ->values(); // [40, 30, 25]

        $this->assertCount(3, $shares);
        $this->assertEquals($expected->all(), $shares->pluck('amount')->all());

        // Al quedar saldo sin asignar, la factura sigue 'pending'
        $this->assertSame('pending', $bill->refresh()->status);
    }



    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
