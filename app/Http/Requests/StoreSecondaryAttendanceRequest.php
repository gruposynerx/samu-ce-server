<?php

namespace App\Http\Requests;

use App\Enums\GenderCodeEnum;
use App\Enums\RequesterTypeEnum;
use App\Enums\ResourceEnum;
use App\Enums\TicketTypeEnum;
use App\Enums\TimeUnitEnum;
use App\Enums\TransferReasonEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSecondaryAttendanceRequest extends FormRequest
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
            // Dados do chamado
            'ticket_type_id' => ['required', 'numeric', 'in:' . TicketTypeEnum::SECONDARY_OCCURRENCE->value],
            'opening_at' => ['required', 'date'],
            'city_id' => ['required', 'numeric', 'exists:cities,id'],
            'number_of_victims' => ['nullable', 'numeric'],

            // Dados do solicitante
            'requester' => ['nullable', 'array'],
            'requester.name' => ['required', 'string'],
            'requester.primary_phone' => ['nullable', 'string', 'required_without:requester.secondary_phone'],
            'requester.secondary_phone' => ['nullable', 'string', 'required_without:requester.primary_phone'],
            'requester.council_number' => ['nullable', 'string'],
            'requester.requester_type_id' => ['nullable', 'numeric', 'in:' . RequesterTypeEnum::MEDICAL->value . ',' . RequesterTypeEnum::OTHER_PROFESSIONAL->value],

            // Dados do paciente (Vou receber um array de pacientes, por conta do front-end)
            'patients' => ['nullable', 'array'],
            'patients.*.name' => ['nullable', 'string'],
            'patients.*.age' => ['nullable', 'integer'],
            'patients.*.time_unit_id' => ['nullable', 'integer', new Enum(TimeUnitEnum::class)],
            'patients.*.gender_code' => ['nullable', 'string', new Enum(GenderCodeEnum::class)],

            // Dados da ocorrência
            'observations' => ['nullable', 'string', 'max:3000'],
            'transfer_reason_id' => ['nullable', 'numeric', new Enum(TransferReasonEnum::class)],
            'in_central_bed' => ['nullable', 'boolean'],
            'protocol' => ['nullable', 'string'],
            'diagnostic_hypothesis' => ['required', 'string', 'max:500'],
            'unit_origin_id' => ['required', 'uuid', 'exists:units,id'],
            'unit_destination_id' => ['required', 'uuid', 'exists:units,id'],
            'origin_unit_contact' => ['nullable', 'string'],
            'destination_unit_contact' => ['nullable', 'string'],
            'complement_origin' => ['nullable', 'string'],
            'complement_destination' => ['nullable', 'string'],
            'requested_resource_id' => ['nullable', 'numeric', new Enum(ResourceEnum::class)],
            'transfer_observation' => ['nullable', 'string', 'max:1000'],
            'is_late_occurrence' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_type_id.required' => __('request_messages/store_secondary_attendance.ticket_type_id.required'),
            'ticket_type_id.numeric' => __('request_messages/store_secondary_attendance.ticket_type_id.numeric'),
            'ticket_type_id.in' => __('request_messages/store_secondary_attendance.ticket_type_id.in'),
            'opening_at.required' => __('request_messages/store_secondary_attendance.opening_at.required'),
            'opening_at.date' => __('request_messages/store_secondary_attendance.opening_at.date'),
            'city_id.required' => __('request_messages/store_secondary_attendance.city_id.required'),
            'city_id.numeric' => __('request_messages/store_secondary_attendance.city_id.numeric'),
            'city_id.exists' => __('request_messages/store_secondary_attendance.city_id.exists'),
            'requester.name.required' => __('request_messages/store_secondary_attendance.requester.name.required'),
            'requester.name.string' => __('request_messages/store_secondary_attendance.requester.name.string'),
            'requester.primary_phone.required_without' => __('request_messages/store_secondary_attendance.requester.primary_phone.required'),
            'requester.primary_phone.string' => __('request_messages/store_secondary_attendance.requester.primary_phone.string'),
            'requester.secondary_phone.required_without' => __('request_messages/store_secondary_attendance.requester.secondary_phone.required_without'),
            'requester.secondary_phone.string' => __('request_messages/store_secondary_attendance.requester.secondary_phone.string'),
            'requester.requester_type_id.numeric' => __('request_messages/store_secondary_attendance.requester.requester_type_id.numeric'),
            'requester.requester_type_id.in' => __('request_messages/store_secondary_attendance.requester.requester_type_id.in'),
            'patients.*.name.string' => __('request_messages/store_secondary_attendance.patients.name.string'),
            'patients.*.age.integer' => __('request_messages/store_secondary_attendance.patients.age.integer'),
            'patients.*.age.required' => __('request_messages/store_secondary_attendance.patients.age.required'),
            'patients.*.time_unit_id.integer' => __('request_messages/store_secondary_attendance.patients.time_unit_id.integer'),
            'patients.*.time_unit_id.enum' => __('request_messages/store_secondary_attendance.patients.time_unit_id.enum'),
            'patients.*.time_unit_id.required' => __('request_messages/store_secondary_attendance.patients.time_unit_id.required'),
            'patients.*.gender_code.string' => __('request_messages/store_primary_attendance.patients.gender_code.string'),
            'patients.*.gender_code.required' => __('request_messages/store_primary_attendance.patients.gender_code.required'),
            'patients.*.gender_code.Illuminate\Validation\Rules\Enum' => __('request_messages/store_primary_attendance.patients.gender_code.enum'),
            'observations.string' => __('request_messages/store_secondary_attendance.observations.string'),
            'observations.max' => __('request_messages/store_secondary_attendance.observations.max'),
            'transfer_reason_id.numeric' => __('request_messages/store_secondary_attendance.transfer_reason_id.numeric'),
            'transfer_reason_id.Illuminate\Validation\Rules\Enum' => __('request_messages/store_secondary_attendance.transfer_reason_id.enum'),
            'in_central_bed.boolean' => __('request_messages/store_secondary_attendance.in_central_bed.boolean'),
            'protocol.string' => __('request_messages/store_secondary_attendance.protocol.string'),
            'diagnostic_hypothesis.required' => __('request_messages/store_secondary_attendance.diagnostic_hypothesis.required'),
            'diagnostic_hypothesis.string' => __('request_messages/store_secondary_attendance.diagnostic_hypothesis.string'),
            'diagnostic_hypothesis.max' => __('request_messages/store_secondary_attendance.diagnostic_hypothesis.max'),
            'unit_origin_id.required' => __('request_messages/store_secondary_attendance.unit_origin_id.required'),
            'unit_origin_id.uuid' => __('request_messages/store_secondary_attendance.unit_origin_id.uuid'),
            'unit_origin_id.exists' => __('request_messages/store_secondary_attendance.unit_origin_id.exists'),
            'unit_destination_id.required' => __('request_messages/store_secondary_attendance.unit_destination_id.required'),
            'unit_destination_id.uuid' => __('request_messages/store_secondary_attendance.unit_destination_id.uuid'),
            'unit_destination_id.exists' => __('request_messages/store_secondary_attendance.unit_destination_id.exists'),
            'complement_origin.string' => __('request_messages/store_secondary_attendance.complement_origin.string'),
            'complement_destination.string' => __('request_messages/store_secondary_attendance.complement_destination.string'),
            'requested_resource_id.numeric' => __('request_messages/store_secondary_attendance.requested_resource_id.numeric'),
            'requested_resource_id.Illuminate\Validation\Rules\Enum' => __('request_messages/store_secondary_attendance.requested_resource_id.enum'),
            'transfer_observation.string' => __('request_messages/store_secondary_attendance.transfer_observation.string'),
            'transfer_observation.max' => __('request_messages/store_secondary_attendance.transfer_observation.max'),
        ];
    }
}
