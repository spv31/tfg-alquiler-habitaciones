<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePropertyDetailRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return false;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
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
