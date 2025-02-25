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
      'id'                  => $this->id,
      'address'             => $this->address,
      'cadastral_reference' => $this->cadastral_reference,
      'description'         => $this->description,
      'rental_type'         => $this->rental_type,
      'status'              => $this->status,
      'total_rooms'         => $this->total_rooms,

      'main_image' => route('image.property.show', [
        'property' => $this->id, 
        'filename' => $this->main_image_url
      ]),

      'images' => $this->images_url->map(
        fn($imagePath) =>
        route('private.property_image', ['filename' => $imagePath])
      ),

      'details'             => new PropertyDetailResource($this->whenLoaded('details')),
      'owner'               => $this->whenLoaded('owner', fn() => new UserResource($this->owner)),
      'created_at'          => $this->created_at,
      'updated_at'          => $this->updated_at,
    ];
  }
}
