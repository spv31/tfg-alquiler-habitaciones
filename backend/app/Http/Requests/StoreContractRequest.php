<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
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
            'contract_template_id' => ['required', 'exists:contract_templates,id'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'tenant_id' => ['required', 'exists:users,id'],
            'type' => ['nullable', 'string', 'max:100'], 
            'price' => ['required', 'numeric'],
            'deposit' => ['required', 'numeric'],
            'utilities_included' => ['boolean'],
            'utilities_payer' => ['nullable', 'string'],
            'utilities_proportion' => ['nullable', 'numeric'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'extension_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pending_signature,active,finished'],
            'pdf_path' => ['nullable', 'string'],
            'pdf_path_signed' => ['nullable', 'string'],
            'token_values' => ['required', 'array'],
            'final_content' => ['nullable', 'string'],
        ];
    }
}
