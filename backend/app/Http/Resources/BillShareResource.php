<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillShareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                         => $this->id,
            'tenant'                     => new UserResource($this->whenLoaded('tenant')),
            'amount'                     => $this->amount,
            'status'                     => $this->status,
            'stripe_payment_intent_id'   => $this->stripe_payment_intent_id,
            'stripe_checkout_session_id' => $this->stripe_checkout_session_id,
            'paid_at'                    => optional($this->paid_at)->toDateTimeString(),
            'created_at'                 => $this->created_at->toDateTimeString(),
            'updated_at'                 => $this->updated_at->toDateTimeString(),
            'utility_bill'               => new UtilityBillResource($this->whenLoaded('utilityBill')),
        ];
    }
}
