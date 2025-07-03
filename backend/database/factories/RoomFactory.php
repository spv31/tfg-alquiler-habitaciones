<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'room_number' => 1,
            'description' => $this->faker->sentence(),
            'rental_price' => 350,
            'status' => 'available',
        ];
    }
}
