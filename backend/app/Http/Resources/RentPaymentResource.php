<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'contract'                  => new ContractResource($this->whenLoaded('contract')),
            'period_start'              => $this->period_start->toDateString(),
            'period_end'                => $this->period_end->toDateString(),
            'due_date'                  => $this->due_date->toDateString(),
            'amount'                    => $this->amount,
            'status'                    => $this->status,
            'stripe_payment_intent_id'  => $this->stripe_payment_intent_id,
            'stripe_mandate_id'         => $this->stripe_mandate_id,
            'paid_at'                   => optional($this->paid_at)->toDateTimeString(),
            'payments'                  => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at'                => $this->created_at->toDateTimeString(),
            'updated_at'                => $this->updated_at->toDateTimeString(),
        ];
    }
}
