<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'address' => 'required|string|max:255',
      'cadastral_reference' => 'required|string|max:255|unique:properties,cadastral_reference',
      'description' => 'required|string',
      'rental_type' => 'required|in:full,per_room',
      'total_rooms' => 'required|integer|min:1',
      // Optional
      'purchase_price' => 'nullable|numeric|min:0',
      'is_financed' => 'nullable|boolean',
      'mortgage_cost' => 'nullable|numeric|min:0',
      'purchase_taxes' => 'nullable|numeric|min:0',
      'renovation_cost' => 'nullable|numeric|min:0',
      'furniture_cost' => 'nullable|numeric|min:0',
      'purchase_date' => 'nullable|date',
      'estimated_value' => 'nullable|numeric|min:0',
      'annual_insurance_cost' => 'nullable|numeric|min:0',
      'annual_property_tax' => 'nullable|numeric|min:0',
      'annual_community_fees' => 'nullable|numeric|min:0',
      'annual_waste_tax' => 'nullable|numeric|min:0',
      'income_tax_percentage' => 'nullable|numeric|min:0|max:100',
      'annual_repair_percentage' => 'nullable|numeric|min:0|max:100',
      'rental_price' => 'nullable|numeric|min:0',
      'property_size' => 'nullable|numeric|min:0',
    ];
  }
}
