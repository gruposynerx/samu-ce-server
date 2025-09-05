<?php

namespace App\Http\Requests;

use App\Enums\AttendanceStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceLinkRequest extends FormRequest
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
            'father_link_id' => ['required', 'uuid', Rule::exists('attendances', 'id')->whereNotIn('attendance_status_id', AttendanceStatusEnum::FINISHED_STATUSES)],
            'children_links' => ['required', 'array', 'min:1'],
            'children_links*' => ['required', 'uuid', Rule::exists('attendances', 'id')->where('attendance_status_id', AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION)],
        ];
    }

    public function attributes(): array
    {
        return [
            'father_link_id' => 'atendimento',
            'children_links' => 'atendimento para vínculo',
        ];
    }
}
