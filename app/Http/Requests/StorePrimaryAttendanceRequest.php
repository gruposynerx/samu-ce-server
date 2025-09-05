<?php

namespace App\Http\Requests;

use App\Enums\DistanceTypeEnum;
use App\Enums\GenderCodeEnum;
use App\Enums\LocationTypeEnum;
use App\Enums\RequesterTypeEnum;
use App\Enums\TicketTypeEnum;
use App\Enums\TimeUnitEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePrimaryAttendanceRequest extends FormRequest
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
            'ticket_type_id' => ['required', 'numeric', 'in:' . TicketTypeEnum::PRIMARY_OCCURRENCE->value],
            'opening_at' => ['required', 'date'],
            'city_id' => ['required', 'numeric', 'exists:cities,id'],
            'multiple_victims' => ['nullable', 'boolean'],
            'number_of_victims' => ['nullable', 'numeric'],

            // Dados do solicitante
            'requester' => ['nullable', 'array'],
            'requester.name' => ['required', 'string'],
            'requester.primary_phone' => ['nullable', 'string', 'required'],
            'requester.secondary_phone' => ['nullable', 'string'],
            'requester.requester_type_id' => ['nullable', 'numeric', new Enum(RequesterTypeEnum::class)],

            // Dados do(s) paciente(s)
            'patients' => ['nullable', 'array'],
            'patients.*.name' => ['nullable', 'string'],
            'patients.*.age' => ['required', 'integer'],
            'patients.*.time_unit_id' => ['required', 'integer', new Enum(TimeUnitEnum::class)],
            'patients.*.gender_code' => ['required', 'string', new Enum(GenderCodeEnum::class)],

            // Dados da ocorrência
            'street' => ['required', 'string', 'max:200'],
            'house_number' => ['nullable', 'string', 'max:20'],
            'neighborhood' => ['required', 'string', 'max:100'],
            'reference_place' => ['nullable', 'string', 'max:2000'],
            'primary_complaint' => ['required', 'string'],
            'observations' => ['nullable', 'string', 'max:3000'],
            'distance_type_id' => ['nullable', 'numeric', new Enum(DistanceTypeEnum::class)],
            'location_type_id' => ['nullable', 'numeric', new Enum(LocationTypeEnum::class)],
            'location_type_description' => ['nullable', 'string'],
            'is_late_occurrence' => ['nullable', 'boolean'],

            'geolocation' => ['sometimes', 'nullable', 'array'],
            'geolocation.address' => ['sometimes', 'nullable', 'array'],
            'geolocation.address.street' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.state' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.city' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.neighborhood' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.postal_code' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.street_number' => ['sometimes', 'nullable', 'string'],
            'geolocation.place_id' => ['sometimes', 'nullable', 'string'],
            'geolocation.location' => ['sometimes', 'nullable', 'array'],
            'geolocation.viewport' => ['sometimes', 'nullable', 'array'],
            'geolocation.formatted_address' => ['sometimes', 'nullable', 'string'],
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
            'ticket_type_id.required' => __('request_messages/store_primary_attendance.ticket_type_id.required'),
            'ticket_type_id.numeric' => __('request_messages/store_primary_attendance.ticket_type_id.numeric'),
            'ticket_type_id.in' => __('request_messages/store_primary_attendance.ticket_type_id.in'),
            'opening_at.required' => __('request_messages/store_primary_attendance.opening_at.required'),
            'opening_at.date' => __('request_messages/store_primary_attendance.opening_at.date'),
            'city_id.required' => __('request_messages/store_primary_attendance.city_id.required'),
            'city_id.numeric' => __('request_messages/store_primary_attendance.city_id.numeric'),
            'city_id.exists' => __('request_messages/store_primary_attendance.city_id.exists'),
            'multiple_victims.boolean' => __('request_messages/store_primary_attendance.multiple_victims.boolean'),
            'number_of_victims.numeric' => __('request_messages/store_primary_attendance.number_of_victims.numeric'),
            'requester.name.required' => __('request_messages/store_primary_attendance.requester.name.required'),
            'requester.name.string' => __('request_messages/store_primary_attendance.requester.name.string'),
            'requester.primary_phone.required' => __('request_messages/store_primary_attendance.requester.primary_phone.required'),
            'requester.primary_phone.string' => __('request_messages/store_primary_attendance.requester.primary_phone.string'),
            'requester.secondary_phone.string' => __('request_messages/store_primary_attendance.requester.secondary_phone.string'),
            'requester.requester_type_id.numeric' => __('request_messages/store_primary_attendance.requester.requester_type_id.numeric'),
            'requester.requester_type_id.Illuminate\Validation\Rules\Enum' => __('request_messages/store_primary_attendance.requester.requester_type_id.enum'),
            'patients.*.name.string' => __('request_messages/store_primary_attendance.patients.name.string'),
            'patients.*.age.integer' => __('request_messages/store_primary_attendance.patients.age.integer'),
            'patients.*.age.required' => __('request_messages/store_primary_attendance.patients.age.required'),
            'patients.*.time_unit_id.integer' => __('request_messages/store_primary_attendance.patients.time_unit_id.integer'),
            'patients.*.time_unit_id.Illuminate\Validation\Rules\Enum' => __('request_messages/store_primary_attendance.patients.time_unit_id.enum'),
            'patients.*.time_unit_id.required' => __('request_messages/store_primary_attendance.patients.time_unit_id.required'),
            'patients.*.gender_code.string' => __('request_messages/store_primary_attendance.patients.gender_code.string'),
            'patients.*.gender_code.required' => __('request_messages/store_primary_attendance.patients.gender_code.required'),
            'patients.*.gender_code.Illuminate\Validation\Rules\Enum' => __('request_messages/store_primary_attendance.patients.gender_code.enum'),
            'street.required' => __('request_messages/store_primary_attendance.street.required'),
            'street.string' => __('request_messages/store_primary_attendance.street.string'),
            'street.max' => __('request_messages/store_primary_attendance.street.max'),
            'house_number.string' => __('request_messages/store_primary_attendance.house_number.string'),
            'house_number.max' => __('request_messages/store_primary_attendance.house_number.max'),
            'neighborhood.required' => __('request_messages/store_primary_attendance.neighborhood.required'),
            'neighborhood.string' => __('request_messages/store_primary_attendance.neighborhood.string'),
            'neighborhood.max' => __('request_messages/store_primary_attendance.neighborhood.max'),
            'reference_place.string' => __('request_messages/store_primary_attendance.reference_place.string'),
            'reference_place.max' => __('request_messages/store_primary_attendance.reference_place.max'),
            'primary_complaint.required' => __('request_messages/store_primary_attendance.primary_complaint.required'),
            'primary_complaint.string' => __('request_messages/store_primary_attendance.primary_complaint.string'),
            'observations.string' => __('request_messages/store_primary_attendance.observations.string'),
            'observations.max' => __('request_messages/store_primary_attendance.observations.max'),
            'distance_type_id.numeric' => __('request_messages/store_primary_attendance.distance_type_id.numeric'),
            'distance_type_id.Illuminate\Validation\Rules\Enum' => __('request_messages/store_primary_attendance.distance_type_id.enum'),
            'location_type_id.numeric' => __('request_messages/store_primary_attendance.location_type_id.numeric'),
            'location_type_id.Illuminate\Validation\Rules\Enum' => __('request_messages/store_primary_attendance.location_type_id.enum'),
            'location_type_description.string' => __('request_messages/store_primary_attendance.location_type_description.string'),
        ];
    }
}
