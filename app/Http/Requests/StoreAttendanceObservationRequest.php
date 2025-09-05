<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceObservationRequest extends FormRequest
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
            'attendance_id' => 'required|uuid|exists:attendances,id',
            'observation' => 'required|string|max:3000',
            'sent_by_app' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_id.required' => 'É obrigatório informar o atendimento.',
            'attendance_id.uuid' => 'O atendimento informado é inválido.',
            'attendance_id.exists' => 'O atendimento informado não existe.',
            'observation.required' => 'É obrigatório informar a observação.',
            'observation.string' => 'A observação informada é inválida.',
            'observation.max' => 'A observação deve ter no máximo 3000 caracteres.',
            'sent_by_app.boolean' => 'O campo sent_by_app deve ser um booleano.',
        ];
    }
}
