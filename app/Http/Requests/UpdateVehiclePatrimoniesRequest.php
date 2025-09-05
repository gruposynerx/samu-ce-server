<?php

namespace App\Http\Requests;

use App\Enums\PatrimonyStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehiclePatrimoniesRequest extends FormRequest
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
            'patrimonies' => ['present', 'array'],
            'patrimonies.*' => ['sometimes', 'string',
                Rule::exists('patrimonies', 'id')->where('patrimony_status_id', PatrimonyStatusEnum::AVAILABLE)->where(function ($query) {
                    $query->whereNull('vehicle_id')
                        ->orWhere('vehicle_id', $this->route('id'));
                }),
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
