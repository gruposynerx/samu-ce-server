<?php

namespace App\Http\Requests;

use App\Enums\ScheduleTypeEnum;
use App\Rules\SchedulableExists;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UserSchedulesRequest extends FormRequest
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
            'professionals' => ['required', 'array', 'min:1'],
            'professionals.*.id' => ['required', 'uuid', 'exists:users,id'],
            'professionals.*.link' => ['nullable', 'string'],
            'professionals.*.occupation_code' => ['nullable', 'string'],
            'dates' => ['required', 'array', 'min:1'],
            'dates.*.prev_start_date' => ['required', 'date_format:Y-m-d\TH:i'],
            'dates.*.prev_end_date' => ['required', 'date_format:Y-m-d\TH:i'],
            'dates.*.shift_id' => ['nullable',  'integer', 'exists:shifts,id'],
            
            'urc_id' => ['required', 'uuid', 'exists:urgency_regulation_centers,id'],
            'base_id' => ['nullable', 'uuid', 'exists:bases,id'],
            'position_jobs_id' => ['nullable', 'uuid', 'exists:position_jobs,id'],
            'regional_group_id' => 'nullable|uuid|exists:regional_groups,id'
        ];
    }

public function messages(): array
{
    return [
        'professionals.required' => 'É obrigatório informar ao menos um profissional.',
        'professionals.array'    => 'O campo de profissionais deve ser uma lista.',
        'professionals.*.uuid'   => 'Cada profissional deve ter um UUID válido.',
        'professionals.*.exists' => 'Profissional não encontrado no sistema.',

        'dates.required' => 'As datas são obrigatórias.',
        'dates.array'    => 'O campo de datas deve ser uma lista.',

        'dates.*.prev_start_date.required' => 'A data de início é obrigatória.',
        'dates.*.prev_start_date.date_format' => 'Formato inválido para data de início. Use "YYYY-MM-DDTHH:MM".',

        'dates.*.prev_end_date.required' => 'A data de término é obrigatória.',
        'dates.*.prev_end_date.date_format' => 'Formato inválido para data de término. Use "YYYY-MM-DDTHH:MM".',

        'urc_id.required' => 'O campo URC é obrigatório.',
        'urc_id.uuid'     => 'O campo URC deve ser um UUID válido.',
        'urc_id.exists'   => 'URC não encontrada.',

    ];
}

}
