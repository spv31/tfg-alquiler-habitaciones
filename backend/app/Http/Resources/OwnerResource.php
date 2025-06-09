<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avatarUrl = null;

        if ($this->profile_image_url) {
            $avatarUrl = route('image.user.show', [
                'user'     => $this->id,
                'filename' => $this->profile_image_url,
            ]);
        }
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'email'       => $this->email,
            'phone'       => $this->phone_number,
            'profile_image' => $avatarUrl,
        ];
    }
}
