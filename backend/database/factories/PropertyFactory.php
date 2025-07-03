<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'address' => $this->faker->address(),
            'cadastral_reference' => Str::uuid(),
            'description' => $this->faker->sentence(),
            'rental_type' => 'full',     
            'status' => 'available',
            'total_rooms' => 3,
        ];
    }
}
