<?php

namespace App\Http\Requests;

use App\Enums\ActionTypeEnum;
use App\Enums\AttendanceStatusEnum;
use App\Enums\ClosingTypeEnum;
use App\Enums\DistanceTypeEnum;
use App\Enums\GenderCodeEnum;
use App\Enums\LocationTypeEnum;
use App\Enums\NatureTypeEnum;
use App\Enums\PriorityTypeEnum;
use App\Enums\RequesterTypeEnum;
use App\Enums\ThrombolyticIndicatedAppliedEnum;
use App\Enums\ThrombolyticIndicatedRecommendedEnum;
use App\Enums\TicketTypeEnum;
use App\Enums\TimeUnitEnum;
use App\Enums\TransferReasonEnum;
use App\Enums\VehicleMovementCodeEnum;
use App\Enums\VehicleTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Query parameters
 */
class IndexAttendanceConsultationRequest extends FormRequest
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
            'list_all' => 'nullable|boolean',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'attendance_number' => 'nullable|string',
            'attendance_status_id' => ['nullable', 'integer', new Enum(AttendanceStatusEnum::class)],
            'user_id' => 'nullable|string|exists:users,id',
            'patient_name' => 'nullable|string',
            'initial_birth_date' => 'nullable|integer',
            'final_birth_date' => 'nullable|integer',
            'time_unit_id' => ['nullable', 'integer', new Enum(TimeUnitEnum::class), 'required_with:initial_birth_date,final_birth_date'],
            'gender_code' => ['nullable', 'string', new Enum(GenderCodeEnum::class)],
            'requesting_phone' => 'nullable|string',
            'cities' => ['nullable', 'array'],
            'cities.*' => 'integer|exists:cities,id',
            'neighborhood' => 'nullable|string',
            'vehicles' => ['nullable', 'array'],
            'vehicles.*' => 'uuid|exists:vehicles,id',
            'vehicles_types' => ['nullable', 'array'],
            'vehicles_types.*' => ['integer', new Enum(VehicleTypeEnum::class)],
            'street' => 'nullable|string',
            'supporting_organizations_medical_regulation' => 'nullable|array',
            'supporting_organizations_scene_recording' => 'nullable|array',
            'priority_types' => ['nullable', 'array'],
            'priority_types.*' => ['nullable', 'integer', new Enum(PriorityTypeEnum::class)],
            'vehicle_movement_codes' => ['nullable', 'array'],
            'vehicle_movement_codes.*' => ['nullable', 'integer', new Enum(VehicleMovementCodeEnum::class)],
            'nature_types' => ['nullable', 'array'],
            'nature_types.*' => ['nullable', 'integer', new Enum(NatureTypeEnum::class)],
            'ticket_types' => ['nullable', 'array'],
            'ticket_types.*' => ['nullable', 'numeric', new Enum(TicketTypeEnum::class)],
            'diagnostic_hypothesis' => ['nullable', 'array'],
            'action_types' => ['nullable', 'array'],
            'action_types.*' => ['nullable', 'integer', new Enum(ActionTypeEnum::class)],
            'requester_types' => ['nullable', 'array'],
            'requester_types.*' => ['nullable', 'integer', new Enum(RequesterTypeEnum::class)],
            'requesting_name' => 'nullable|string',
            'distance_types' => ['nullable', 'array'],
            'distance_types.*' => ['nullable', 'integer', new Enum(DistanceTypeEnum::class)],
            'location_types' => ['nullable', 'array'],
            'location_types.*' => ['nullable', 'numeric', new Enum(LocationTypeEnum::class)],
            'closing_types' => ['nullable', 'array'],
            'closing_types.*' => ['nullable', 'integer', new Enum(ClosingTypeEnum::class)],
            'units_origin' => ['nullable', 'array'],
            'units_origin.*' => 'uuid|exists:units,id',
            'units_destination' => ['nullable', 'array'],
            'units_destination.*' => 'uuid|exists:units,id',
            'antecedents' => ['nullable', 'array'],
            'antecedents.*' => 'integer|exists:antecedents_types,id',
            'transfer_reason_id' => ['nullable', 'numeric', new Enum(TransferReasonEnum::class)],
            'bases' => ['nullable', 'array'],
            'bases.*' => 'uuid|exists:bases,id',
            'diagnostic_hypotheses' => ['nullable', 'array'],
            'diagnostic_hypotheses.*' => 'integer|exists:diagnostic_hypotheses,id',
            'thrombolytic_recommended' => ['nullable', 'array'],
            'thrombolytic_recommended.*' => ['nullable', 'integer', new Enum(ThrombolyticIndicatedRecommendedEnum::class)],
            'thrombolytic_applied' => ['nullable', 'array'],
            'thrombolytic_applied.*' => ['nullable', 'integer', new Enum(ThrombolyticIndicatedAppliedEnum::class)],
        ];
    }
}
