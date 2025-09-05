<?php

namespace App\Http\Requests;

use App\Enums\VehicleStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreVehicleStatusHistoryRequest extends FormRequest
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
            'vehicle_status_id' => ['required', 'integer', new Enum(VehicleStatusEnum::class)],
            'base_id' => ['nullable', 'uuid', 'exists:bases,id'],
            'description' => ['nullable', 'string', 'max:1000', 'required_if:vehicle_status_id,' . VehicleStatusEnum::UNAVAILABLE->value],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_status_id.required' => 'O status da viatura é obrigatório.',
            'vehicle_status_id.integer' => 'O status da viatura deve ser um número inteiro.',
            'vehicle_status_id.in' => 'O status da viatura deve ser um caso válido.',
            'description.required_if' => 'O motivo é obrigatória quando o status da viatura é indisponível.',
            'description.string' => 'O motivo deve ser uma string.',
            'description.max' => 'O motivo deve ter no máximo 1000 caracteres.',
            'base_id.uuid' => 'A base ser um UUID válido.',
            'base_id.exists' => 'A base deve existir.',
        ];
    }
}
