<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehiclePatrimonyRequest extends FormRequest
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
            'patrimonies' => ['required', 'array'],
            'patrimonies.*' => ['required', 'string',
                Rule::exists('patrimonies', 'id')->where('is_active', true)->whereNull('vehicle_id'),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'patrimony_id.required' => 'É necessário informar o equipamento.',
            'patrimony_id.exists' => 'O equipamento informado não existe ou já está vinculado a uma viatura.',
            'patrimony_id.string' => 'O equipamento informado é inválido.',
        ];
    }
}
