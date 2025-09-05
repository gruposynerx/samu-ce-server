<?php

namespace App\Http\Requests;

use App\Enums\ScheduleTypeEnum;
use App\Rules\SchedulableExists;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ScheduleEventRequest extends FormRequest
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
        'attachment'            => ['nullable', 'string'],
        'justification' => ['nullable', 'string'],
        'user_schedule_id'         => ['nullable', 'string', 'exists:user_schedules,id'],
        'reverse_user_schedule_id'         => ['nullable', 'string', 'exists:user_schedules,id'],
        'schedule_event_type_id'         => ['nullable', 'string', 'exists:schedule_event_types,id'],
        'professional_id'         => ['nullable', 'uuid', 'exists:users,id'],
        'reverse_professional_id'         => ['nullable', 'uuid', 'exists:users,id'],
    ];
}


public function messages(): array
{
    return [
        'attachment.string' => 'O campo anexo deve ser um texto.',
        'justification.string' => 'O campo justificativa deve ser um texto.',
        'user_schedule_id.string' => 'O user_schedule_id deve ser um texto.',
        'user_schedule_id.exists' => 'O campo user_schedule_id é inválido.',
        'reverse_user_schedule_id.string' => 'O campo reverse_user_schedule_id deve ser um texto.',
        'reverse_user_schedule_id.exists' => 'O campo reverse_user_schedule_id é inválido.',
        'schedule_event_type_id.string' => 'O campo schedule_event_type_id deve ser um texto.',
        'schedule_event_type_id.exists' => 'O campo reveschedule_event_type_idse_user_schedule_id é inválido.',
        'professional_id.uuid' => 'O campo profissional deve ser um UUID válido.',
        'professional_id.exists' => 'O profissional selecionado é inválido.',
        'reverse_professional_id.uuid' => 'O campo profissional reverso deve ser um UUID válido.',
        'reverse_professional_id.exists' => 'O profissional reverso selecionado é inválido.',
    ];
}

}
