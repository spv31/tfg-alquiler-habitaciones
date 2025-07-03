<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Models\Property;
use App\Models\User;
use App\Services\PropertyServices;
use App\Services\UploadFilesService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PropertyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_property(): void
    {
        $owner = User::factory()->owner()->create();
        $this->actingAs($owner);

        $property = Property::factory()->create(['user_id' => $owner->id]);

        $propSrv = Mockery::mock(PropertyServices::class);
        $propSrv->shouldReceive('createProperty')->andReturn($property);
        $this->instance(PropertyServices::class, $propSrv);
        $this->instance(UploadFilesService::class, Mockery::mock(UploadFilesService::class)->shouldIgnoreMissing());

        $payload = [
            'address' => 'Calle 1',
            'cadastral_reference' => 'REF123',
            'description' => 'Desc',
            'rental_type' => 'full',
            'total_rooms' => 1,
        ];

        $this->postJson('/api/properties', $payload)
            ->assertCreated()
            ->assertJsonPath('message_key', 'property_created')
            ->assertJsonPath('property.id', $property->id);
    }

    public function test_index_returns_only_properties_of_authenticated_owner(): void
    {
        $owner = User::factory()->owner()->create();
        $otherUser = User::factory()->owner()->create();

        Property::factory()->count(2)->create(['user_id' => $owner->id]);
        Property::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($owner)
            ->getJson('/api/properties')
            ->assertOk()
            ->assertJsonPath('meta.total', 2);
    }
}
