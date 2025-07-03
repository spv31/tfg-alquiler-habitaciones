<?php

namespace Tests\Unit;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\PropertyTenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyTenantServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PropertyTenantService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PropertyTenantService;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_assign_tenant_to_full_property_sets_status(): void
    {
        $prop = Property::factory()->create(['rental_type' => 'full', 'status' => 'available']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->service->assignTenant($prop, $tenant);

        $this->assertDatabaseHas('property_tenants', [
            'rentable_id' => $prop->id,
            'tenant_id' => $tenant->id
        ]);
        $this->assertEquals('occupied', $prop->fresh()->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_assign_tenant_to_room_updates_property_status(): void
    {
        $prop = Property::factory()->create(['rental_type' => 'per_room', 'total_rooms' => 2, 'status' => 'available']);
        $room = Room::factory()->create(['property_id' => $prop->id, 'status' => 'available']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->service->assignTenant($room, $tenant);

        $this->assertEquals('partially_occupied', $prop->fresh()->status);
        $this->assertEquals('occupied', $room->fresh()->status);
    }
}
