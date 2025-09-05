<?php

namespace App\Http\Requests;

use App\Enums\SatisfactionScaleEnum;
use App\Enums\SatisfactionTimeAmbulanceArriveEnum;
use App\Enums\SatisfactionTimeSpentPhoneEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreRequesterSatisfactionRequest extends FormRequest
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
            'satisfaction_time_ambulance_arrive_id' => ['nullable', 'integer', new Enum(SatisfactionTimeAmbulanceArriveEnum::class)],
            'satisfaction_time_spent_phone_id' => ['nullable', 'integer', new Enum(SatisfactionTimeSpentPhoneEnum::class)],
            'scale_attendance_provided_mecs_team' => ['nullable', 'integer', new Enum(SatisfactionScaleEnum::class)],
            'scale_satisfaction_service_offered' => ['required', 'integer', new Enum(SatisfactionScaleEnum::class)],
            'scale_telephone_attendance' => ['nullable', 'integer', new Enum(SatisfactionScaleEnum::class)],
            'requester_sugestion' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
