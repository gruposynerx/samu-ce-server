<?php

namespace App\Http\Requests;

use App\Enums\DutyReportTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Query parameters
 */
class IndexDutyReportRequest extends FormRequest
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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|before_or_equal:today',
            'duty_report_type_id' => ['nullable', 'integer', new Enum(DutyReportTypeEnum::class)],
            'all_data' => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            'start_date' => 'data inicial',
            'end_date' => 'data final',
            'duty_report_type_id' => 'tipo de relatório',
            'all_data' => 'todos os dados',
        ];
    }
}
