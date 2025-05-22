<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
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
            'contract_template_id' => ['sometimes', 'required', 'exists:contract_templates,id'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'tenant_id' => ['sometimes', 'required', 'exists:users,id'],
            'type' => ['nullable', 'string', 'max:100'],
            'price' => ['sometimes', 'required', 'numeric'],
            'deposit' => ['sometimes', 'required', 'numeric'],
            'utilities_included' => ['boolean'],
            'utilities_payer' => ['nullable', 'string'],
            'utilities_proportion' => ['nullable', 'numeric'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['nullable', 'date'],
            'extension_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'required', 'in:draft,signed_by_owner,active,finished'],
            'pdf_path' => ['nullable', 'string'],
            'pdf_path_signed' => ['nullable', 'string'],
            'final_content' => ['nullable', 'string'],
        ];
    }
}
