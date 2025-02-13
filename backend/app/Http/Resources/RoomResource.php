<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
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
      'property_id' => $this->property_id,
      'room_number' => $this->room_number,
      'description' => $this->description,
      'rental_price' => $this->rental_price,
      'status' => $this->status,

      'main_image' => route('private.room_image', [
        'filename' => $this->main_image_url
      ]),

      'images' => $this->images_url->map(
        fn($imagePath) =>
        route('private.room_image', ['filename' => $imagePath])
      ),

      'tenant' => $this->whenLoaded('tenant', fn() => new TenantResource($this->tenant)),
      'invitations' => InvitationResource::collection($this->whenLoaded('invitations')),
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ];
  }
}
