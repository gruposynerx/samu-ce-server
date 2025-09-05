<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
            'updated_by_vehicle_status_and_base_modal' => ['nullable', 'boolean'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('vehicles', 'code')->ignore($this->route('id')), 'required_without:updated_by_vehicle_status_and_base_modal'],
            'license_plate' => ['nullable', 'string', 'max:255', Rule::unique('vehicles', 'license_plate')->ignore($this->route('id')), 'required_without:updated_by_vehicle_status_and_base_modal'],
            'base_id' => ['nullable', 'uuid', 'exists:bases,id'],
            'chassis' => ['nullable', 'string', 'max:255'],
            'general_availability' => ['nullable', 'boolean'],
            'tracking_device_imei' => [
                'nullable',
                'string',
                'max:255',
                'required_with:tracking_system_id',
                Rule::unique('vehicles', 'tracking_device_imei')->ignore($this->route('id')),
            ],
            'tracking_system_id' => [
                'nullable',
                'numeric',
                'required_with:tracking_device_imei',
                Rule::unique('vehicles', 'tracking_system_id')->ignore($this->route('id')),
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
            'vehicle_type_id.required' => 'O tipo de viatura é obrigatório.',
            'vehicle_type_id.numeric' => 'O tipo de viatura deve ser numérico.',
            'vehicle_type_id.enum' => 'O tipo de viatura deve ser um caso válido.',
            'code.required' => 'O código é obrigatório.',
            'code.string' => 'O código deve ser uma string.',
            'code.max' => 'O código deve ter no máximo 255 caracteres.',
            'code.unique' => 'O código deve ser único.',
            'license_plate.required' => 'A placa é obrigatório.',
            'license_plate.string' => 'A placa deve ser uma string.',
            'license_plate.max' => 'A placa deve ter no máximo 255 caracteres.',
            'license_plate.unique' => 'A placa deve ser única.',
            'base_id.required' => 'A base é obrigatória.',
            'base_id.uuid' => 'A base ser um UUID válido.',
            'base_id.exists' => 'A base deve existir.',
            'chassis.string' => 'O nome deve ser uma string.',
            'chassis.max' => 'O nome deve ter no máximo 255 caracteres.',
            'general_availability.boolean' => 'A disponibilidade geral deve ser um booleano.',
            'tracking_device_imei.string' => 'O IMEI rastreador deve ser uma string.',
            'tracking_device_imei.max' => 'O IMEI rastreador deve ter no máximo 255 caracteres.',
            'tracking_device_imei.required_with' => 'O IMEI rastreador é obrigatório com o ID rastreamento.',
            'tracking_device_imei.unique' => 'O IMEI já está vinculado á outra VTR.',
            'tracking_system_id.numeric' => 'O ID rastreamento deve ser um número.',
            'tracking_system_id.required_with' => 'O ID rastreamento é obrigatório com o IMEI rastreador.',
            'tracking_system_id.unique' => 'O ID rastreamento já está vinculado á outra VTR.',
        ];
    }
}
