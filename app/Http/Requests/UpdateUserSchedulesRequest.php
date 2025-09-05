<?php

namespace App\Http\Requests;

use App\Enums\ScheduleTypeEnum;
use App\Rules\SchedulableExists;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUserSchedulesRequest extends FormRequest
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
        'professionals'   => ['nullable', 'array', 'min:1'],
        'professionals.*' => ['uuid', 'exists:users,id'],
        'dates'           => ['nullable', 'array', 'min:1'],
        'dates.*.prev_start_date' => ['nullable', 'date_format:Y-m-d\TH:i'],
        'dates.*.prev_end_date'   => ['nullable', 'date_format:Y-m-d\TH:i'],
        'dates.*.shift_id'   => ['nullable',  'integer', 'exists:shifts,id'],
        'urc_id'          => ['nullable', 'uuid', 'exists:urgency_regulation_centers,id'],
        'base_id'         => ['nullable', 'uuid', 'exists:bases,id'],
        'link'            => ['nullable', 'string'],
        'occupation_code' => ['nullable', 'string'],
        'observation' => ['nullable', 'string'],
        'starts_at'       => ['nullable', 'date'],
        'ends_at'         => ['nullable', 'date', 'after:starts_at'],
        'link' => ['nullable', 'date'],
    ];
}


public function messages(): array
{
    return [
        'professionals.array'    => 'O campo de profissionais deve ser uma lista.',
        'professionals.*.uuid'   => 'Cada profissional deve ter um UUID válido.',
        'professionals.*.exists' => 'Profissional não encontrado no sistema.',
        'dates.array'    => 'O campo de datas deve ser uma lista.',
        'dates.*.prev_start_date.date_format' => 'Formato inválido para data de início. Use "YYYY-MM-DDTHH:MM".',
        'dates.*.prev_end_date.date_format' => 'Formato inválido para data de término. Use "YYYY-MM-DDTHH:MM".',
        'urc_id.uuid'     => 'O campo URC deve ser um UUID válido.',
        'urc_id.exists'   => 'URC não encontrada.',
        'base_id.uuid'     => 'O campo base deve ser um UUID válido.',
        'base_id.exists'   => 'Base não encontrada.',
        'link.string' => 'O link deve ser uma string.',
        'occupation_code.string' => 'O Código de ocupação deve ser uma string.',
        'observation.string' => 'A observação deve ser uma string.',
        'ends_at.after' => 'A data de término geral deve ser posterior à data de início geral'
    ];
}

}
