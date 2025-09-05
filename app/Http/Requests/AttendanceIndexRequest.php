<?php

namespace App\Http\Requests;

use App\Enums\AttendanceStatusEnum;
use Illuminate\Validation\Rules\Enum;

/**
 * Query parameters
 */
class AttendanceIndexRequest extends SearchRequest
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
        return array_merge(parent::rules(), [
            'attendance_status_id' => ['sometimes', new Enum(AttendanceStatusEnum::class)],
            'exclude_finished_attendances' => 'sometimes|boolean',
        ]);
    }
}
