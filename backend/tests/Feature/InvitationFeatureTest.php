<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Property;
use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_owner_can_send_an_invitation(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $invitation = Invitation::factory()->make([
            'owner_id' => $owner->id,
            'rentable_id' => $property->id,
            'rentable_type' => Property::class,
            'email' => 'guest@example.com',
        ]);

        $mock = Mockery::mock(InvitationService::class);
        $mock->shouldReceive('createInvitation')->once()->andReturn($invitation);
        $this->app->instance(InvitationService::class, $mock);

        $this->actingAs($owner)
            ->postJson('/api/invitations', [
                'email' => 'guest@example.com',
                'property_id' => $property->id,
            ])
            ->assertCreated()
            ->assertJsonPath('message_key', 'invitation_sent')
            ->assertJsonPath('invitation.email', 'guest@example.com');
    }

    public function test_index_returns_only_authenticated_owner_invitations(): void
    {
        $owner = User::factory()->owner()->create();
        $otherOwner = User::factory()->owner()->create();

        $inv1 = Invitation::factory()->create(['owner_id' => $owner->id]);
        Invitation::factory()->create(['owner_id' => $otherOwner->id]);

        $this->actingAs($owner)
            ->getJson('/api/invitations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $inv1->id);
    }
}
