<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class PropertyResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    Log::info('Datos de PropertyResource antes de enviarse:', [
      'id' => $this->id,
      'address' => $this->address,
      'main_image_url' => $this->main_image_url,
    ]);

    return [
      'id'                  => $this->id,
      'address'             => $this->address,
      'cadastral_reference' => $this->cadastral_reference,
      'description'         => $this->description,
      'rental_type'         => $this->rental_type,
      'status'              => $this->status,
      'total_rooms'         => $this->total_rooms,

      'main_image_url' => $this->main_image_url,

      'details'             => new PropertyDetailResource($this->whenLoaded('details')),
      'owner'               => $this->whenLoaded('owner', fn() => new UserResource($this->owner)),
      'created_at'          => $this->created_at,
      'updated_at'          => $this->updated_at,
    ];
  }
}
