<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UtilityBillFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_utility_bill(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);

        $payload = [
            'category' => 'utility',
            'total_amount' => 120.55,
            'issue_date' => '2025-04-01',
            'due_date' => '2025-04-15',
            'property_id'  => $property->id,
        ];

        $this->actingAs($owner)
            ->postJson('/api/utility-bills', $payload)
            ->assertCreated()
            ->assertJsonPath('total_amount', '120.55');

        $this->assertDatabaseHas('utility_bills', [
            'property_id'  => $property->id,
            'total_amount' => 120.55,
        ]);
    }

    public function test_owner_can_filter_utility_bills_by_property(): void
    {
        $owner = User::factory()->owner()->create();
        $property = Property::factory()->create(['user_id' => $owner->id]);
        $other = Property::factory()->create();

        $this->actingAs($owner)->postJson('/api/utility-bills', [
            'category'     => 'utility',
            'total_amount' => 80,
            'issue_date'   => '2025-03-01',
            'due_date'     => '2025-03-15',
            'property_id'  => $property->id,
        ]);

        $this->actingAs($owner)->postJson('/api/utility-bills', [
            'category'     => 'utility',
            'total_amount' => 99,
            'issue_date'   => '2025-03-01',
            'due_date'     => '2025-03-15',
            'property_id'  => $other->id,
        ]);

        $this->actingAs($owner)
            ->getJson("/api/utility-bills?property_id={$property->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.total_amount', '80.00');
    }
}
