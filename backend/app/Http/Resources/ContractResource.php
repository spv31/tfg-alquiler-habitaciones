<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
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
            'contract_template_id' => $this->contract_template_id,
            'property_id' => $this->property_id,
            'room_id' => $this->room_id,
            'tenant_id' => $this->tenant_id,
            'type' => $this->type,
            'price' => $this->price,
            'deposit' => $this->deposit,
            'utilities_included' => $this->utilities_included,
            'utilities_payer' => $this->utilities_payer,
            'utilities_proportion' => $this->utilities_proportion,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'extension_date' => $this->extension_date,
            'status' => $this->status,
            'pdf_path' => $this->pdf_path,
            'pdf_path_signed' => $this->pdf_path_signed,
            'final_content' => $this->final_content,
            'token_values' => $this->token_values,
            'owner_iban'               => $this->owner_iban,
            'tenant_iban'              => $this->tenant_iban,
            'stripe_payment_method_id' => $this->stripe_payment_method_id,
            'pdf_path_signed_owner'    => $this->pdf_path_signed_owner,
            'pdf_path_signed_tenant'   => $this->pdf_path_signed_tenant,
            'signed_by_owner_at'       => optional($this->signed_by_owner_at)->toDateTimeString(),
            'signed_by_tenant_at'      => optional($this->signed_by_tenant_at)->toDateTimeString(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
