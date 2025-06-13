<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bill_share_id'   => 'required_without:rent_payment_id|exists:bill_shares,id',
            'rent_payment_id' => 'required_without:bill_share_id|exists:rent_payments,id',
            'amount'          => 'required|numeric|min:0',
        ];
    }
}
