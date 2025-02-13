<?php

use App\Models\Property;
use App\Models\Room;
use App\Services;

class RoomService
{
	public function __construct() {}

	public function createRoom(Property $property, array $validatedData)
	{
		$roomNumber = $property->rooms->count() + 1;

		$room = $property->rooms()->create([
			'room_number' => $roomNumber,
			'description' => $validatedData['description'],
			'rental_price' => $validatedData['rental_price'],
			'status' => 'available'
		]);

		return $room;
	}

	public function updateRoom(Room $room, array $validatedData)
	{

	}

	public function deleteRoom(Room $room)
	{

	}
}
