<?php

namespace App\Http\Requests;

use App\Enums\AttendanceStatusEnum;
use Illuminate\Validation\Rules\Enum;

/**
 * Query parameters
 */
class OcurrenceTypesByAttendanceStatusRequest extends DashboardRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendance_status_id' => ['required', new Enum(AttendanceStatusEnum::class)],
            'urc_id' => 'required|exists:urgency_regulation_centers,id|uuid',
        ];
    }
}
