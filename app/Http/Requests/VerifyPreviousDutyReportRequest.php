<?php

namespace App\Http\Requests;

use App\Enums\DutyReportTypeEnum;
use App\Enums\PeriodTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class VerifyPreviousDutyReportRequest extends FormRequest
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
            'record_at' => 'required|date|before_or_equal:today',
            'period_type_id' => ['required', 'integer', new Enum(PeriodTypeEnum::class)],
            'duty_report_type_id' => ['required', 'integer', new Enum(DutyReportTypeEnum::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'record_at' => 'data do registro',
            'period_type_id' => 'período',
            'duty_report_type_id' => 'tipo de relatório',
        ];
    }
}
