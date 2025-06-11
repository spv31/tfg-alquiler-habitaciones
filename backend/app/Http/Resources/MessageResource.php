<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'body'        => $this->body,
            'sender_id'   => $this->sender_id,
            'sender_name' => $this->sender->name,
            'sent_at'     => $this->created_at->toIso8601String(),
            'read_at'     => optional($this->read_at)?->toIso8601String(),
            'metadata'    => $this->metadata,
        ];
    }
}
