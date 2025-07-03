<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Room;

class RoomService
{
	public function __construct() {}

	/**
	 * Creates a new room assigned to a property
	 * 
	 * @param App\Models\Property $property
	 * @param array $validatedData
	 */
	public function createRoom(Property $property, array $validatedData)
	{
		$roomNumber = $property->rooms()->count() + 1;

		$room = $property->rooms()->create([
			'room_number' => $roomNumber,
			'description' => $validatedData['description'],
			'rental_price' => $validatedData['rental_price'],
			'status' => 'available'
		]);

		return $room;
	}

	/**
	 * Updates a room assigned to a property
	 * 
	 * @param App\Models\Room $room
	 * @param array $validatedData
	 * @return array<bool|Room|null>
	 */
	public function updateRoom(Room $room, array $validatedData)
	{
		$updated = $room->update($validatedData);
		$room = $room->fresh();
		return [$room, $updated];
	}

	/**
	 * Deletes a room assigned to a property
	 * 
	 * @param App\Models\Room $room
	 * @return bool|null
	 */
	public function deleteRoom(Room $room)
	{
		return $room->delete();
	}

	/**
	 * Updates room's status
	 * 
	 * @param App\Models\Room $room
	 * @param mixed $newStatus
	 * @return bool
	 */
	public function changeStatus(Room $room, $newStatus)
	{
		return $room->update([
			'status' => $newStatus,
		]);
	}
}
