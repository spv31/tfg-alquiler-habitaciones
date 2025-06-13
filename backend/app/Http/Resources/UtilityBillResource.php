<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UtilityBillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'property'          => new PropertyResource($this->whenLoaded('property')),
            'room'              => $this->whenLoaded('room') ? new RoomResource($this->room) : null,
            'owner'             => new OwnerResource($this->whenLoaded('owner')),
            'issue_date'        => $this->issue_date->toDateString(),
            'due_date'          => $this->due_date->toDateString(),
            'period_start'      => optional($this->period_start)->toDateString(),
            'period_end'        => optional($this->period_end)->toDateString(),
            'total_amount'      => $this->total_amount,
            'category'          => $this->category,
            'description'       => $this->description,
            'attachment_url'    => $this->attachment_path ? url("/api/files/{$this->attachment_path}") : null,
            'status'            => $this->status,
            'bill_shares'       => BillShareResource::collection($this->whenLoaded('billShares')),
            'created_at'        => $this->created_at->toDateTimeString(),
            'updated_at'        => $this->updated_at->toDateTimeString(),
        ];
    }
}
