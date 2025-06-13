<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRentPaymentRequest extends FormRequest
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
            'period_start'  => 'sometimes|date',
            'period_end'    => 'sometimes|date|after_or_equal:period_start',
            'due_date'      => 'sometimes|date|after_or_equal:period_end',
            'amount'        => 'sometimes|numeric|min:0',

            // Allows changing state or associating Stripe's IDs with PATH
            'status'                    => 'sometimes|string|in:pending,paid,cancelled',
            'stripe_payment_intent_id'  => 'sometimes|string',
            'stripe_mandate_id'         => 'sometimes|string',
            'paid_at'                   => 'sometimes|date',
            'description'               => 'nullable|string|max:255',
        ];
    }
}
