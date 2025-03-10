<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyDetailResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'property_id' => $this->property_id,
      'purchase_price' => $this->purchase_price,
      'is_financed' => $this->is_financed,
      'mortgage_cost' => $this->mortgage_cost,
      'purchase_taxes' => $this->purchase_taxes,
      'renovation_cost' => $this->renovation_cost,
      'furniture_cost' => $this->furniture_cost,
      'purchase_date' => $this->purchase_date,
      'estimated_value' => $this->estimated_value,
      'annual_insurance_cost' => $this->annual_insurance_cost,
      'annual_property_tax' => $this->annual_property_tax,
      'annual_community_fees' => $this->annual_community_fees,
      'annual_waste_tax' => $this->annual_waste_tax,
      'income_tax_percentage' => $this->income_tax_percentage,
      'annual_repair_percentage' => $this->annual_repair_percentage,
      'rental_price' => $this->rental_price,
      'property_size' => $this->property_size,
    ];
  }
}
