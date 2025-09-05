<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceCancellationRecordRequest extends FormRequest
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
            'requester' => 'required|string|max:100',
            'reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_id.required' => 'É obrigatório informar o ID do atendimento',
            'attendance_id.uuid' => 'O campo ID do atendimento deve ser um UUID válido',
            'attendance_id.exists' => 'O atendimento não existe',
            'requester.required' => 'É obrigatório informar o solicitante do cancelamento',
            'requester.string' => 'O solicitante do cancelamento deve ser uma string',
            'requester.max' => 'O solicitante do cancelamento deve ter no máximo 100 caracteres',
            'reason.required' => 'É obrigatório informar o motivo do cancelamento',
            'reason.string' => 'O motivo do cancelamento deve ser uma string',
            'reason.max' => 'O motivo do cancelamento deve ter no máximo 100 caracteres',
        ];
    }
}
