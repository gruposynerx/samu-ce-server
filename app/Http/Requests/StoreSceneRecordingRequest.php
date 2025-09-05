<?php

namespace App\Http\Requests;

use App\Enums\AntecedentTypeEnum;
use App\Enums\BleedingTypeEnum;
use App\Enums\ClosingTypeEnum;
use App\Enums\ConductEnum;
use App\Enums\GenderCodeEnum;
use App\Enums\PriorityTypeEnum;
use App\Enums\SkinColorationTypeEnum;
use App\Enums\SweatingTypeEnum;
use App\Enums\TimeUnitEnum;
use App\Enums\TransferReasonEnum;
use App\Enums\WoundPlaceTypeEnum;
use App\Enums\WoundTypeEnum;
use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreSceneRecordingRequest extends FormRequest
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
            'attendance_id' => ['required', 'uuid', 'exists:attendances,id'],

            'patient' => ['nullable', 'array'],
            'patient.name' => ['nullable', 'string', 'max:255'],
            'patient.gender_code' => ['nullable', 'string', new Enum(GenderCodeEnum::class)],
            'patient.*.age' => ['nullable', 'integer'],
            'patient.*.time_unit_id' => ['nullable', 'integer', new Enum(TimeUnitEnum::class)],

            'diagnostic_hypotheses' => ['required', 'array', 'min:1'],
            'diagnostic_hypothesis.*.diagnostic_hypothesis_id' => ['required', 'numeric', 'exists:diagnostic_hypotheses,id'],
            'diagnostic_hypothesis.*.nature_type_id' => ['required', 'numeric', 'exists:nature_types,id'],
            'diagnostic_hypothesis.*.applied' => ['nullable', 'string'],
            'diagnostic_hypothesis.*.recommended' => ['nullable', 'string'],

            'scene_description' => ['nullable', 'string', 'max:2000'],
            'icd_code' => ['nullable', 'string', 'max:255', 'exists:icds,code'],
            'victim_type' => ['nullable', 'string'],
            'security_equipment' => ['nullable', 'string'],

            'metrics' => ['required', 'array'],
            'metrics.*.start_at' => ['required', 'date'],
            'metrics.*.diagnostic_evaluation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'metrics.*.systolic_blood_pressure' => ['sometimes', 'nullable', 'integer', 'digits_between:1,3'],
            'metrics.*.diastolic_blood_pressure' => ['sometimes', 'nullable', 'integer', 'digits_between:1,3'],
            'metrics.*.heart_rate' => ['sometimes', 'nullable', 'integer'],
            'metrics.*.respiratory_frequency' => ['sometimes', 'nullable', 'integer'],
            'metrics.*.temperature' => ['sometimes', 'nullable', 'numeric'],
            'metrics.*.oxygen_saturation' => ['sometimes', 'nullable', 'string', 'max:10'],
            'metrics.*.glasgow_scale' => ['sometimes', 'nullable', 'integer'],

            'bleeding_type_id' => ['nullable', 'integer', new Enum(BleedingTypeEnum::class)],
            'sweating_type_id' => ['nullable', 'integer', new Enum(SweatingTypeEnum::class)],
            'skin_coloration_type_id' => ['nullable', 'integer', new Enum(SkinColorationTypeEnum::class)],
            'priority_type_id' => ['nullable', 'integer', new Enum(PriorityTypeEnum::class)],
            'observations' => ['nullable', 'string', 'max:2000'],

            'wounds' => ['nullable', 'array'],
            'wounds.*.wound_type_id' => ['nullable', 'integer', new Enum(WoundTypeEnum::class)],
            'wounds.*.wound_place_type_id' => ['nullable', 'integer', new Enum(WoundPlaceTypeEnum::class)],

            'procedures' => ['nullable', 'array'],
            'procedures.*.procedure_code' => ['nullable', 'string', 'exists:procedures,code'],
            'procedures.*.observations' => ['nullable', 'string', 'max:255'],

            'medicines' => ['nullable', 'array'],
            'medicines.*.medicine_id' => ['nullable', 'uuid', 'exists:medicines,id'],
            'medicines.*.quantity' => ['nullable', 'integer'],
            'medicines.*.observations' => ['nullable', 'string', 'max:255'],

            'antecedentsTypes' => ['nullable', 'array'],
            'antecedentsTypes.*.antecedent_type_id' => ['nullable', 'integer', new Enum(AntecedentTypeEnum::class)],

            'allergy' => ['nullable', 'string', 'max:255'],

            'support_needed' => ['nullable', 'boolean', 'required_if:closed,false'],
            'support_needed_description' => ['nullable', 'array'],
            'support_needed_description.*' => ['string', 'max:255'],
            'is_accident_at_work' => ['nullable', 'boolean', 'required_if:closed,false'],
            'conduct_types' => ['nullable', 'array', 'required_if:closed,false'],
            'unit_destination_id' => ['nullable', 'uuid', 'exists:units,id'],
            'destination_unit_contact' => ['nullable', 'string'],
            'vacancy_type_id' => ['nullable', 'integer', new Enum(TransferReasonEnum::class), Rule::requiredIf(function () {
                $attendance = Attendance::find($this->input('attendance_id'));
                $isAttendanceWithRemoval = in_array('Atendimento com Remoção', $this->input('conduct_types', []));

                return $attendance->attendable_type === 'secondary_attendance' && $isAttendanceWithRemoval;
            })],

            'conducts' => ['nullable', 'array'],
            'conducts.*.conduct_id' => ['nullable', 'integer', new Enum(ConductEnum::class)],
            'conducts.*.conduct_description' => ['nullable', 'string', 'max:255'],

            'closed' => ['nullable', 'boolean'],
            'closing_type_id' => ['nullable', 'integer', new Enum(ClosingTypeEnum::class)],
            'closed_justification' => ['nullable', 'string', 'max:1000'],
            'death_at' => ['nullable', 'date'],
            'death_type' => ['nullable', 'string', 'max:255'],
            'death_professional' => ['nullable', 'string', 'max:255'],
            'death_professional_registration_number' => ['nullable', 'string', 'max:255'],
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
            'attendance_id.required' => 'É necessário informar o ID do atendimento.',
            'attendance_id.uuid' => 'O ID do atendimento deve ser um UUID válido.',
            'attendance_id.exists' => 'O atendimento informado não existe.',
            'patient.name.string' => 'O nome do paciente deve ser uma string.',
            'patient.name.max' => 'O nome do paciente deve ter no máximo 255 caracteres.',
            'patient.gender_code.integer' => 'O sexo do paciente deve ser um inteiro',
            'patient.gender_code.enum' => 'O sexo do paciente deve ser um caso valido.',
            'patient.age.string' => 'A idade do paciente deve ser uma string.',
            'nature_type_id.required' => 'É necessário informar o tipo de natureza.',
            'nature_type_id.integer' => 'O tipo de natureza deve ser um inteiro.',
            'nature_type_id.enum' => 'O tipo de natureza deve ser um caso valido.',
            'diagnostic_hypothesis_id.required' => 'É necessário informar o ID da hipótese diagnóstica.',
            'diagnostic_hypothesis_id.uuid' => 'O ID da hipótese diagnóstica deve ser um UUID válido.',
            'diagnostic_hypothesis_id.exists' => 'A hipótese diagnóstica informada não existe.',
            'scene_description.string' => 'A descrição da cena deve ser uma string.',
            'scene_description.max' => 'A descrição da cena deve ter no máximo 2000 caracteres.',
            'icd_code.string' => 'O código CID deve ser uma string.',
            'icd_code.max' => 'O código CID deve ter no máximo 255 caracteres.',
            'victim_type.string' => 'O tipo de vítima deve ser uma string.',
            'security_equipment.string' => 'O equipamento de segurança deve ser uma string.',
            'metrics.required' => 'É necessário informar os sinais vitais.',
            'metrics.*.start_at.date' => 'A data de início deve ser uma data válida.',
            'metrics.*.start_at.required' => 'É necessário informar a data de início.',
            'metrics.*.diagnostic_evaluation.required' => 'É necessário informar a avaliação diagnóstica.',
            'metrics.*.diagnostic_evaluation.string' => 'A avaliação diagnóstica deve ser uma string.',
            'metrics.*.diagnostic_evaluation.max' => 'A avaliação diagnóstica deve ter no máximo 255 caracteres.',
            'metrics.*.systolic_blood_pressure.required' => 'É necessário informar a pressão arterial sistólica.',
            'metrics.*.systolic_blood_pressure.integer' => 'A pressão arterial sistólica deve ser um inteiro.',
            'metrics.*.systolic_blood_pressure.digits_between' => 'A pressão arterial sistólica deve ter entre 1 e 3 dígitos.',
            'metrics.*.diastolic_blood_pressure.required' => 'É necessário informar a pressão arterial diastólica.',
            'metrics.*.diastolic_blood_pressure.integer' => 'A pressão arterial diastólica deve ser um inteiro.',
            'metrics.*.diastolic_blood_pressure.digits_between' => 'A pressão arterial diastólica deve ter entre 1 e 3 dígitos.',
            'metrics.*.heart_rate.required' => 'É necessário informar a frequência cardíaca.',
            'metrics.*.heart_rate.integer' => 'A frequência cardíaca deve ser um inteiro.',
            'metrics.*.respiratory_frequency.required' => 'É necessário informar a frequência respiratória.',
            'metrics.*.respiratory_frequency.integer' => 'A frequência respiratória deve ser um inteiro.',
            'metrics.*.temperature.required' => 'É necessário informar a temperatura.',
            'metrics.*.temperature.numeric' => 'A temperatura deve ser um número.',
            'metrics.*.oxygen_saturation.required' => 'É necessário informar a saturação de oxigênio.',
            'metrics.*.oxygen_saturation.integer' => 'A saturação de oxigênio deve ser um inteiro.',
            'metrics.*.glasgow_scale.required' => 'É necessário informar a escala de glasgow.',
            'metrics.*.glasgow_scale.integer' => 'A escala de glasgow deve ser um inteiro.',
            'bleeding_type_id.integer' => 'O tipo de sangramento deve ser um inteiro.',
            'bleeding_type_id.enum' => 'O tipo de sangramento deve ser um caso valido.',
            'sweating_type_id.integer' => 'O tipo de suor deve ser um inteiro.',
            'sweating_type_id.enum' => 'O tipo de suor deve ser um caso valido.',
            'skin_coloration_type_id.integer' => 'O tipo de coloração da pele deve ser um inteiro.',
            'skin_coloration_type_id.enum' => 'O tipo de coloração da pele deve ser um caso valido.',
            'priority_type_id.integer' => 'O tipo de prioridade deve ser um inteiro.',
            'priority_type_id.enum' => 'O tipo de prioridade deve ser um caso valido.',
            'observations.string' => 'As observações devem ser uma string.',
            'observations.max' => 'As observações devem ter no máximo 2000 caracteres.',
            'wounds.*.wound_type_id.integer' => 'O tipo de ferimento deve ser um inteiro.',
            'wounds.*.wound_type_id.enum' => 'O tipo de ferimento deve ser um caso valido.',
            'wounds.*.wound_place_type_id.integer' => 'O local do ferimento deve ser um inteiro.',
            'wounds.*.wound_place_type_id.enum' => 'O local do ferimento deve ser um caso valido.',
            'procedures.*.procedure_code.string' => 'O código do procedimento deve ser uma string.',
            'procedures.*.procedure_code.exists' => 'O código do procedimento informado não existe.',
            'procedures.*.observations.string' => 'As observações do procedimento devem ser uma string.',
            'procedures.*.observations.max' => 'As observações do procedimento devem ter no máximo 255 caracteres.',
            'medicines.*.medicine_id.uuid' => 'O ID do medicamento deve ser um UUID válido.',
            'medicines.*.medicine_id.exists' => 'O medicamento informado não existe.',
            'medicines.*.quantity.integer' => 'A quantidade do medicamento deve ser um inteiro.',
            'medicines.*.observations.string' => 'As observações do medicamento devem ser uma string.',
            'medicines.*.observations.max' => 'As observações do medicamento devem ter no máximo 255 caracteres.',
            'antecedent_type_id.integer' => 'O tipo de antecedente deve ser um inteiro.',
            'antecedent_type_id.enum' => 'O tipo de antecedente deve ser um caso valido.',
            'allergy.string' => 'A alergia deve ser uma string.',
            'allergy.max' => 'A alergia deve ter no máximo 255 caracteres.',
            'support_needed.required' => 'É necessário informar se o paciente necessita de suporte.',
            'support_needed.boolean' => 'O suporte deve ser um booleano.',
            'support_needed_description' => 'O campo de descrição do suporte deve ser um array de strings.',
            'support_needed_description.*.string' => 'A descrição do suporte deve ser uma string.',
            'support_needed_description.*.max' => 'A descrição do suporte deve ter no máximo 255 caracteres.',
            'conduct_types.required' => 'É necessário informar os tipos de conduta.',
            'conduct_types.array' => 'Os tipos de conduta devem ser um array.',
            'unit_destination_id.uuid' => 'O ID da unidade de destino deve ser um UUID válido.',
            'unit_destination_id.exists' => 'A unidade de destino informada não existe.',
            'vacancy_type_id.integer' => 'O tipo de vaga deve ser um inteiro.',
            'vacancy_type_id.enum' => 'O tipo de vaga deve ser um caso valido.',
            'vacancy_type_id.required' => 'É necessário informar o tipo de vaga.',
            'conducts.*.conduct_id.integer' => 'O tipo de conduta deve ser um inteiro.',
            'conducts.*.conduct_id.enum' => 'O tipo de conduta deve ser um caso valido.',
            'conducts.*.conduct_description.string' => 'A descrição da conduta deve ser uma string.',
            'conducts.*.conduct_description.max' => 'A descrição da conduta deve ter no máximo 255 caracteres.',
            'closed.boolean' => 'O fechamento deve ser um booleano.',
            'closing_type_id.integer' => 'O tipo de fechamento deve ser um inteiro.',
            'closing_type_id.enum' => 'O tipo de fechamento deve ser um caso valido.',
            'death_at.date' => 'A data de óbito deve ser uma data válida.',
            'death_type.string' => 'O tipo de óbito deve ser uma string.',
            'death_type.max' => 'O tipo de óbito deve ter no máximo 255 caracteres.',
            'death_professional.string' => 'O profissional que constatou o óbito deve ser uma string.',
            'death_professional.max' => 'O profissional que constatou o óbito deve ter no máximo 255 caracteres.',
        ];
    }
}
