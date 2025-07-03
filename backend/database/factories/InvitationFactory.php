<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        $property = Property::factory()->create();

        return [
            'email' => $this->faker->unique()->safeEmail(),
            'token' => Str::random(32),
            'rentable_id' => $property->id,
            'rentable_type' => Property::class,
            'owner_id' => $property->user_id,
            'status' => 'pending',
        ];
    }
}
