<?php

namespace Tests\Unit;

use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use App\Services\PropertyServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PropertyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PropertyServices $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PropertyServices;
        $this->be(User::factory()->create(['role' => 'owner']));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_create_property_with_optional_details(): void
    {
        $data = Property::factory()->make()->toArray() + [
            'total_rooms' => 3,
            'purchase_price' => 120000,
            'property_size' => 95,
        ];

        $property = $this->service->createProperty($data);

        $this->assertDatabaseHas('properties', ['id' => $property->id]);
        $this->assertDatabaseHas('property_details', [
            'property_id'  => $property->id,
            'purchase_price' => 120000,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_update_property_fails_if_total_rooms_lower_than_existing(): void
    {
        $property = Property::factory()->create(['total_rooms' => 3]);
        Room::factory()->count(3)->create(['property_id' => $property->id]);

        $this->expectException(ValidationException::class);

        $this->service->updateProperty($property, ['total_rooms' => 2]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_change_status_propagates_to_rooms(): void
    {
        $property = Property::factory()->create(['rental_type' => 'per_room']);
        Room::factory()->count(2)->create(['property_id' => $property->id]);

        $this->service->changeStatus($property, 'unavailable');

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'unavailable'
        ]);
        $this->assertDatabaseCount('rooms', 2);
        $this->assertDatabaseHas('rooms', ['property_id' => $property->id, 'status' => 'unavailable']);
    }
}
