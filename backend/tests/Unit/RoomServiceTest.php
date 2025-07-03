<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Services\RoomService;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoomServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RoomService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RoomService;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_create_room_increments_room_number(): void
    {
        $property = Property::factory()->create();
        $this->service->createRoom($property, [
            'description' => 'Primera',
            'rental_price' => 300
        ]);
        $room2 = $this->service->createRoom($property, [
            'description' => 'Segunda',
            'rental_price' => 320
        ]);

        $this->assertEquals(2, $room2->room_number);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function test_change_status_of_room(): void
    {
        $property = Property::factory()->create();
        $room = $this->service->createRoom($property, [
            'description' => 'Hab',
            'rental_price' => 250
        ]);

        $this->service->changeStatus($room, 'occupied');

        $this->assertDatabaseHas('rooms', ['id' => $room->id, 'status' => 'occupied']);
    }
}
