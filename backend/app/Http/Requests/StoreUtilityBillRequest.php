<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUtilityBillRequest extends FormRequest
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
            'category'          => 'required|string|in:utility,general,tax',
            'remit_to_tenants'  => 'sometimes|boolean',
            'description'       => 'nullable|string|max:255',
            'status'            => 'sometimes|string|in:pending,settled,split',
            'total_amount'      => 'required|numeric|min:0',
            'issue_date'        => 'required|date',
            'due_date'          => 'required|date|after_or_equal:issue_date',
            'period_start'      => 'nullable|date',
            'period_end'        => 'nullable|date|after_or_equal:period_start',
            'attachment'        => 'nullable|file|mimes:jpg,png,pdf',
            'property_id'       => 'required|exists:properties,id',
            'room_id'           => 'nullable|exists:rooms,id',
        ];
    }
}
