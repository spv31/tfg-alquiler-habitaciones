<?php

namespace App\Services;

use App\Exceptions\InvalidRentableTypeException;
use App\Models\Property;
use App\Models\PropertyTenant;
use App\Models\Room;
use App\Models\User;

class PropertyTenantService
{
	public function assignTenant($rentable, User $user)
	{
		PropertyTenant::create([
			'rentable_id' => $rentable->id,
			'rentable_type' => $rentable instanceof Room ? Room::class : Property::class,
			'tenant_id' => $user->id,
		]);

		if ($rentable instanceof Property) {
			$this->assignTenantToProperty($rentable);
		} else {
			$this->assignTenantToRoom($rentable);
		}
	}

	public function assignTenantToProperty(Property $property)
	{
		if ($property->rental_type !== 'full') {
			throw new InvalidRentableTypeException();
		}
		$property->update(['status' => 'occupied']);
	}

	public function assignTenantToRoom(Room $room)
	{
		if ($room->property->rental_type !== 'per_room') {
			throw new InvalidRentableTypeException();
		}
		$room->update(attributes: ['status' => 'occupied']);

		$property = $room->property;
		$totalRooms = $property->total_rooms;
		$occupiedRooms = $property->rooms->where('status', 'occupied')->count();

		if ($occupiedRooms === 0) {
			$property->update(['status' => 'available']);
		} elseif ($occupiedRooms < $totalRooms) {
			$property->update(['status' => 'partially_occupied']);
		} else {
			$property->update(['status' => 'occupied']);
		}
	}

	public function removeTenant($rentable, User $user)
	{
		$tenantAssignment = PropertyTenant::where('rentable_id', $rentable->id)
			->where('rentable_type', $rentable instanceof Room ? Room::class : Property::class)
			->where('tenant_id', $user->id)
			->first();

		if ($tenantAssignment) {
			$tenantAssignment->delete();
		}

		if ($rentable instanceof Room) {
			$rentable->update(['status' => 'available']);

			// Update status of rooms'property
			$property = $rentable->property;
			$totalRooms = $property->rooms()->count();
			$occupiedRooms = $property->rooms()->where('status', 'occupied')->count();

			if ($occupiedRooms === 0) {
				$property->update(['status' => 'available']);
			} elseif ($occupiedRooms < $totalRooms) {
				$property->update(['status' => 'partially_occupied']);
			} else {
				$property->update(['status' => 'occupied']);
			}
		} else {
			$rentable->update(['status' => 'available']);
		}
	}
}
