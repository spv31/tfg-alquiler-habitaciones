<?php

namespace App\Http\Resources;

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
      'name' => $this->name,
      'email' => $this->email,
      'user_type' => $this->user_type,
      'profile_picture' => route('private.profile_image', [
        'filename' => $this->profile_image_url 
      ]),
      'phone_number' => $this->phone_number,
    ];
  }
}
