<?php

namespace App\Http\Requests;

use App\Rules\UserBelongsToCurrentUrcDutyReport;
use App\Rules\UserHasProfile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDutyReportRequest extends FormRequest
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
            'internal_complications' => 'nullable|string|max:5000|required_without_all:external_complications,compliments,events',
            'external_complications' => 'nullable|string|max:5000|required_without_all:internal_complications,compliments,events',
            'compliments' => 'nullable|string|max:5000|required_without_all:internal_complications,external_complications,events',
            'events' => 'nullable|string|max:5000|required_without_all:internal_complications,external_complications,compliments',
            'medical_regulators' => 'required|array',
            'medical_regulators.*.user_id' => ['required', 'string', 'exists:users,id', new UserHasProfile(['medic']), new UserBelongsToCurrentUrcDutyReport($this->duty_report_type_id)],
            'medical_regulators.*.current_role_slug' => 'required|string|in:medic',
            'radio_operators' => 'required|array',
            'radio_operators.*.user_id' => ['required', 'string', 'exists:users,id', new UserHasProfile(['radio-operator']), new UserBelongsToCurrentUrcDutyReport($this->duty_report_type_id)],
            'radio_operators.*.current_role_slug' => 'required|string|in:radio-operator',
        ];
    }

    public function attributes(): array
    {
        return [
            'internal_complications' => 'intercorrências internas',
            'external_complications' => 'intercorrências externas',
            'compliments' => 'elogios',
            'events' => 'eventos',
            'medical_regulators' => 'médicos reguladores',
            'medical_regulators.*.id' => 'médico regulador',
            'medical_regulators.*.current_role_slug' => 'perfil',
            'radio_operators' => 'Operadores de Frota/Gerentes de Frota',
            'radio_operators.*.id' => 'Operador de Frota/Gerente de Frota',
            'radio_operators.*.current_role_slug' => 'perfil',
        ];
    }
}
