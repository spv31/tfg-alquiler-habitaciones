<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'amount'                    => $this->amount,
            'method'                    => $this->method,
            'stripe_payment_intent_id'  => $this->stripe_payment_intent_id,
            'paid_at'                   => optional($this->paid_at)->toDateTimeString(),

            'bill_share'                => $this->whenLoaded('billShare', value: fn() => new BillShareResource($this->billShare)),
            'rent_payment'              => $this->whenLoaded('rentPayment', value: fn() => new RentPaymentResource($this->rentPayment)),

            'created_at'                => $this->created_at->toDateTimeString(),
            'updated_at'                => $this->updated_at->toDateTimeString(),
        ];
    }
}
