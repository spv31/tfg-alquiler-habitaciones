<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
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
      'description' => 'sometimes|required|string',
      'rental_price' => 'sometimes|required|numeric|min:0',
      'main_image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
    ];
  }
}
