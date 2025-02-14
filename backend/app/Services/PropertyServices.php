<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyDetail;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
		unset($validatedData['total_rooms']);

		$property->update([
			'address' => $validatedData['address'] ?? $property->address,
			'cadastral_reference' => $validatedData['cadastral_reference'] ?? $property->cadastral_reference,
			'description' => $validatedData['description'] ?? $property->description,
			'rental_type' => $validatedData['rental_type'] ?? $property->rental_type,
			'status' => $validatedData['status'] ?? $property->status,
		]);

		$optionalData = array_intersect_key($validatedData, array_flip(self::$propertyDetailFields));

		if (!empty($optionalData)) {
			$property->details()->updateOrCreate(['property_id' => $property->id], $optionalData);
		}

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
		return $property->update([
			'status' => $newStatus,
		]);
	}
}
