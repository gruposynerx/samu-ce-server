<?php

namespace App\Http\Requests;

use App\Enums\ActionTypeEnum;
use App\Enums\ConsciousnessLevelEnum;
use App\Enums\PriorityTypeEnum;
use App\Enums\RespirationTypeEnum;
use App\Enums\VehicleMovementCodeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreMedicalRegulationRequest extends FormRequest
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
            'attendance_id' => ['required', 'exists:attendances,id'],
            'medical_regulation' => ['required', 'string', 'max:5000'],
            'diagnostic_hypotheses' => ['required', 'array', 'min:1'],
            'diagnostic_hypothesis.*.diagnostic_hypothesis_id' => ['required', 'numeric', 'exists:diagnostic_hypotheses,id'],
            'diagnostic_hypothesis.*.nature_type_id' => ['required', 'numeric', 'exists:nature_types,id'],
            'diagnostic_hypothesis.*.applied' => ['nullable', 'string'],
            'diagnostic_hypothesis.*.recommended' => ['nullable', 'string'],
            'priority_type_id' => ['nullable', 'numeric', new Enum(PriorityTypeEnum::class), 'required_if:action_type_id,' . ActionTypeEnum::WITH_INTERVENTION->value],
            'consciousness_level_id' => ['nullable', 'numeric', new Enum(ConsciousnessLevelEnum::class)],
            'respiration_type_id' => ['nullable', 'numeric', new Enum(RespirationTypeEnum::class)],
            'action_type_id' => ['required', 'numeric', new Enum(ActionTypeEnum::class)],
            'action_details' => ['nullable', 'array'],
            'vehicle_movement_code_id' => ['nullable', 'numeric', new Enum(VehicleMovementCodeEnum::class), 'required_if:action_type_id,' . ActionTypeEnum::WITH_INTERVENTION->value],
            'supporting_organizations' => ['nullable', 'array'],
            'destination_unit_contact' => ['nullable', 'string'],
            'destination_unit_id' => ['nullable', 'uuid', 'exists:units,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attendance_id.required' => 'O chamado é obrigatório.',
            'attendance_id.exists' => 'O chamado informado não existe.',
            'medical_regulation.required' => 'A regulação médica é obrigatório.',
            'medical_regulation.string' => 'A regulação médica deve ser uma string.',
            'medical_regulation.max' => 'A regulação médica deve ter no máximo 5000 caracteres.',
            'diagnostic_hypotheses.nature_type_id.required' => 'A natureza é obrigatória.',
            'diagnostic_hypotheses.nature_type_id.numeric' => 'A natureza deve ser um número.',
            'diagnostic_hypotheses.nature_type_id.enum' => 'A natureza deve ser um caso válido',
            'diagnostic_hypotheses.diagnostic_hypothesis_id.required' => 'A hipótese diagnóstica é obrigatória.',
            'diagnostic_hypotheses.diagnostic_hypothesis_id.numeric' => 'A hipótese diagnóstica deve ser um número.',
            'diagnostic_hypotheses.diagnostic_hypothesis_id.exists' => 'A hipótese diagnóstica informada não existe.',
            'diagnostic_hypothesis.recommended.string' => 'A recomendação deve ser uma string',
            'diagnostic_hypothesis.applied.string' => 'A aplicação deve ser uma string',
            'priority_type_id.required_if' => 'A prioridade é obrigatória quando o tipo de ação for com intervenção.',
            'priority_type_id.numeric' => 'A prioridade deve ser um número.',
            'priority_type_id.enum' => 'A prioridade deve ser um caso válido',
            'consciousness_level_id.numeric' => 'O nível de consciência deve ser um número.',
            'consciousness_level_id.enum' => 'O nível de consciência deve ser um caso válido',
            'respiration_type_id.numeric' => 'A respiração deve ser um número.',
            'respiration_type_id.enum' => 'A respiração deve ser um caso válido',
            'action_type_id.required' => 'O tipo de ação é obrigatório.',
            'action_type_id.numeric' => 'O tipo de ação deve ser um número.',
            'action_type_id.enum' => 'O tipo de ação deve ser um caso válido',
            'action_details.array' => 'Os detalhes do tipo de ação deve ser um array.',
            'vehicle_movement_code_id.required_if' => 'O código código de deslocamento da viatura é obrigatório quando o tipo de ação for com intervenção.',
            'vehicle_movement_code_id.numeric' => 'O código código de deslocamento da viatura deve ser um número.',
            'vehicle_movement_code_id.enum' => 'O código código de deslocamento da viatura deve ser um caso válido',
            'supporting_organizations.array' => 'As organizações de apoio devem ser um array.',
        ];
    }
}
