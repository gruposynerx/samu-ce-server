<?php

namespace App\Http\Requests;

use App\Enums\GenderCodeEnum;
use App\Enums\TicketTypeEnum;
use App\Enums\TimeUnitEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreOtherAttendanceRequest extends FormRequest
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
            'ticket_type_id' => [
                'required', 'numeric',
                'in:' .
                    TicketTypeEnum::PRANK_CALL->value . ',' .
                    TicketTypeEnum::INFORMATION->value . ',' .
                    TicketTypeEnum::MISTAKE->value . ',' .
                    TicketTypeEnum::CALL_DROP->value . ',' .
                    TicketTypeEnum::CONTACT_WITH_SAMU_TEAM->value,
            ],
            'opening_at' => ['required', 'date'],
            'city_id' => ['nullable', 'numeric', 'exists:cities,id'],

            // Dados do solicitante
            'requester' => ['nullable', 'array'],
            'requester.name' => ['required', 'string'],
            'requester.primary_phone' => ['nullable', 'string', 'required_without:requester.secondary_phone'],
            'requester.secondary_phone' => ['nullable', 'string', 'required_without:requester.primary_phone'],

            // Dados do paciente (Vou receber um array de pacientes, por conta do front-end)
            'patients' => ['nullable', 'array'],
            'patients.*.name' => ['nullable', 'string'],
            'patients.*.age' => ['nullable', 'integer'],
            'patients.*.time_unit_id' => ['nullable', 'integer', new Enum(TimeUnitEnum::class)],
            'patients.*.gender_code' => ['nullable', 'string', new Enum(GenderCodeEnum::class)],

            // Dados da ocorrência
            'description' => ['nullable', 'string', 'max:3000'],
            'is_late_occurrence' => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'ticket_type_id.required' => __('request_messages/store_other_attendance.ticket_type_id.required'),
            'ticket_type_id.numeric' => __('request_messages/store_other_attendance.ticket_type_id.numeric'),
            'ticket_type_id.in' => __('request_messages/store_other_attendance.ticket_type_id.in'),
            'opening_at.required' => __('request_messages/store_other_attendance.opening_at.required'),
            'opening_at.date' => __('request_messages/store_other_attendance.opening_at.date'),
            'city_id.numeric' => __('request_messages/store_other_attendance.city_id.numeric'),
            'city_id.exists' => __('request_messages/store_other_attendance.city_id.exists'),
            'requester.name.required' => __('request_messages/store_other_attendance.requester.name.required'),
            'requester.name.string' => __('request_messages/store_other_attendance.requester.name.string'),
            'requester.primary_phone.string' => __('request_messages/store_other_attendance.requester.primary_phone.string'),
            'requester.secondary_phone.string' => __('request_messages/store_other_attendance.requester.secondary_phone.string'),
            'patients.*.name.string' => __('request_messages/store_other_attendance.patients.name.string'),
            'patients.*.age.integer' => __('request_messages/store_other_attendance.patients.age.integer'),
            'patients.*.time_unit_id.integer' => __('request_messages/store_other_attendance.patients.time_unit_id.integer'),
            'description.string' => __('request_messages/store_other_attendance.description.string'),
            'description.max' => __('request_messages/store_other_attendance.description.max'),
        ];
    }
}
