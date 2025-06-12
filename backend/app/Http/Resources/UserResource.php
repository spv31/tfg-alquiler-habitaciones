<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'user_type'   => $this->user_type,
            'identifier'  => $this->identifier,
            'role'        => $this->role,
            'phone_number'       => $this->phone_number,
            'address'     => $this->address,
            'email_verified_at' => $this->email_verified_at,
            'profile_image_filename' => $this->profile_image_filename,
            'profile_image_url'     => $this->profile_image_url,
            'created_at'  => $this->created_at,
        ];
    }
}
