<?php

namespace App\Http\Resources;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
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
      'name' => $this->name,
      'email' => $this->email,
      'user_type' => $this->user_type,
      'profile_picture' => $this->profile_image_url,
      'phone_number' => $this->phone_number,
      'room_id' => optional($this->rental)->rentable_type === Room::class ? $this->rental->room_number : null,
    ];
  }
}
