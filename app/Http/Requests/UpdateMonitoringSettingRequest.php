<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMonitoringSettingRequest extends FormRequest
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
            'link_validation_time' => 'required|integer|min:1',
            'enable_attendance_monitoring' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'link_validation_time.required' => 'O tempo de expiração é obrigatório.',
            'link_validation_time.integer' => 'O tempo de expiração deve ser um número inteiro.',
            'link_validation_time.min' => 'O tempo de expiração deve ser no mínimo 1.',
            'enable_attendance_monitoring.required' => 'Deve ser obrigatório informar se o monitoramento de atendimentos está habilitado.',
            'enable_attendance_monitoring.boolean' => 'O campo de habilitação do monitoramento de atendimentos deve ser verdadeiro ou falso.',
        ];
    }
}
