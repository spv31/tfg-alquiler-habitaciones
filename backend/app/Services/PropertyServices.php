<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PropertyServices
{

	/**
	 * Optional fields of propertyDetailFields
	 * 
	 * @var array
	 */
	protected static array $propertyDetailFields = [
		'purchase_price',
		'is_financed',
		'mortgage_cost',
		'purchase_taxes',
		'renovation_cost',
		'furniture_cost',
		'purchase_date',
		'estimated_value',
		'annual_insurance_cost',
		'annual_property_tax',
		'annual_community_fees',
		'annual_waste_tax',
		'income_tax_percentage',
		'annual_repair_percentage',
		'rental_price',
		'property_size',
	];

	public function __construct() {}

	public function createProperty(array $validatedData)
	{
		Log::info('Datos para crear propiedad:', $validatedData);
		$validatedData['status'] = 'available';

		$property = Property::create([
			'user_id' => Auth::id(),
			'address' => $validatedData['address'],
			'cadastral_reference' => $validatedData['cadastral_reference'],
			'description' => $validatedData['description'],
			'rental_type' => $validatedData['rental_type'],
			'status' => $validatedData['status'],
			'total_rooms' => $validatedData['total_rooms'],
		]);

		$optionalData = array_intersect_key($validatedData, array_flip(self::$propertyDetailFields));

		// Will only save $fillable PropertyDetails attributes 
		if (!empty($optionalData)) {
			$property->details()->create($optionalData);
		}

		return $property;
	}

	public function updateProperty(Property $property, array $validatedData)
	{
		Log::info("Intentando actualizar propiedad", ['property_id' => $property->id]);

		$existingRoomsCount = $property->rooms()->count();
		Log::info("Número de habitaciones existentes", ['property_id' => $property->id, 'existing_rooms' => $existingRoomsCount]);

		if (isset($validatedData['total_rooms']) && $validatedData['total_rooms'] < $existingRoomsCount) {
			Log::warning("Error: Intento de reducir el número total de habitaciones por debajo de las existentes", [
				'property_id' => $property->id,
				'requested_total_rooms' => $validatedData['total_rooms'],
				'existing_rooms' => $existingRoomsCount
			]);

			throw ValidationException::withMessages([
				'error_key' => 'total_rooms_too_low',
				'message' => __('No puedes reducir el número total de habitaciones por debajo de las ya creadas.')
			]);
		}

		Log::info("Datos validados para la actualización", ['property_id' => $property->id, 'data' => $validatedData]);

		$property->update([
			'address' => $validatedData['address'] ?? $property->address,
			'cadastral_reference' => $validatedData['cadastral_reference'] ?? $property->cadastral_reference,
			'description' => $validatedData['description'] ?? $property->description,
			'rental_type' => $validatedData['rental_type'] ?? $property->rental_type,
			'status' => $validatedData['status'] ?? $property->status,
			'total_rooms' => $validatedData['total_rooms'] ?? $property->total_rooms,
		]);

		Log::info("Propiedad actualizada con éxito", ['property_id' => $property->id]);

		$optionalData = array_intersect_key($validatedData, array_flip(self::$propertyDetailFields));

		if (!empty($optionalData)) {
			Log::info("Actualizando detalles de la propiedad", ['property_id' => $property->id, 'details' => $optionalData]);
			$property->details()->updateOrCreate(['property_id' => $property->id], $optionalData);
		}

		Log::info("Actualización finalizada", ['property_id' => $property->id]);

		return $property;
	}

	/**
	 * Deletes a property
	 * 
	 * @param \App\Models\Property $property
	 * @return bool|null
	 */
	public function deleteProperty(Property $property)
	{
		return $property->delete();
	}

	/**
	 * Updates properties' status
	 * 
	 * @param \App\Models\Property $property
	 * @param mixed $newStatus
	 * @return bool
	 */
	public function changeStatus(Property $property, $newStatus)
	{
		if ($property->rental_type === 'per_room') {
			Log::info("Cambiando el estado de la propiedad y sus habitaciones", [
				'property_id' => $property->id,
				'new_status' => $newStatus
			]);
			foreach ($property->rooms as $room) {
				$room->update(['status' => $newStatus]);
			}
		}

		return $property->update([
			'status' => $newStatus,
		]);
	}
}
