<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class RoomFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_a_room_until_limit_is_reached(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'total_rooms' => 2,
        ]);

        $this->actingAs($owner);

        $payload = ['description' => 'Room', 'rental_price' => 300];

        $this->postJson("/api/properties/{$property->id}/rooms", $payload)
            ->assertCreated();

        $this->postJson("/api/properties/{$property->id}/rooms", $payload)
            ->assertCreated();

        $this->postJson("/api/properties/{$property->id}/rooms", $payload)
            ->assertStatus(400)
            ->assertJsonPath('error_key', 'rooms_limit_reached');
    }

    public function test_listing_rooms_returns_warning_if_some_missing(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create([
            'user_id' => $owner->id,
            'total_rooms' => 3,
        ]);

        $this->actingAs($owner);

        $this->postJson("/api/properties/{$property->id}/rooms", [
            'description' => 'Only room',
            'rental_price' => 250,
        ]);

        $this->getJson("/api/properties/{$property->id}/rooms")
            ->assertOk()
            ->assertJsonPath('warning.key', 'missing_rooms_warning');
    }
}
