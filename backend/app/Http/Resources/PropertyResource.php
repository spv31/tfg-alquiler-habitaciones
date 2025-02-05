<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'address' => $this->address,
      'cadastral_reference' => $this->cadastral_reference,
      'description' => $this->description,
      'rental_type' => $this->rental_type,
      'status' => $this->status,
      'total_rooms' => $this->total_rooms,
      'details' => new PropertyDetailResource($this->whenLoaded('details')),
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
