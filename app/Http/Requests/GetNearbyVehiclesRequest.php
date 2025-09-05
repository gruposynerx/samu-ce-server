<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetNearbyVehiclesRequest extends FormRequest
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
            'attendance_id' => 'required|string|exists:attendances,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'search' => ['nullable', 'string', 'max:255'],
            'by_disponibility' => ['nullable', 'in:true,false,1,0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attendance_id.required' => 'O ID da ocorrência é obrigatório.',
            'attendance_id.exists' => 'A ocorrência informada não existe.',
            'latitude.required' => 'A latitude é obrigatória.',
            'latitude.numeric' => 'A latitude deve ser um valor numérico.',
            'latitude.between' => 'A latitude deve estar entre -90 e 90.',
            'longitude.required' => 'A longitude é obrigatória.',
            'longitude.numeric' => 'A longitude deve ser um valor numérico.',
            'longitude.between' => 'A longitude deve estar entre -180 e 180.',
            'search.string' => 'A busca deve ser uma string.',
            'search.max' => 'A busca deve ter no máximo 255 caracteres.',
            'by_disponibility.in' => 'O parâmetro de disponibilidade deve ser true, false, 1 ou 0.',
        ];
    }
}
