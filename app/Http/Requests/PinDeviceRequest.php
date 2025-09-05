<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PinDeviceRequest extends FormRequest
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
            'pin' => 'required|string|size:6',
            'mac_address' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'O campo PIN é obrigatório.',
            'pin.size' => 'O campo PIN deve conter 6 caracteres.',
            'mac_address.string' => 'O campo MAC Address deve ser uma string.',
        ];
    }
}
