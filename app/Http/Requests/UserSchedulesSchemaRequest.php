<?php

namespace App\Http\Requests;

use App\Enums\ScheduleTypeEnum;
use App\Rules\SchedulableExists;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UserSchedulesSchemaRequest extends FormRequest
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
            'user_id' => 'required|uuid|exists:users,id',
            'schedulable_id' => [
                'required',
                'uuid',
                'bail',
                new SchedulableExists(),
            ],
            'valid_from' => 'required|date',
            'valid_through' => [
                'required',
                'date',
                'after_or_equal:valid_from',
                'before_or_equal:' . Carbon::parse($this->input('valid_from'))->addMonths(3)->format('Y-m-d'),
            ],
            'days_of_week' => 'required|array',
            'days_of_week.*' => 'integer',
            'clock_in' => 'required|string',
            'clock_out' => 'required|string',
            'schedule_type_id' => ['required', new Enum(ScheduleTypeEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => __('request_messages/store_user_schedule_schema.user_id.required'),
            'user_id.uuid' => __('request_messages/store_user_schedule_schema.user_id.uuid'),
            'user_id.exists' => __('request_messages/store_user_schedule_schema.user_id.exists'),
            'schedulable_id.required' => __('request_messages/store_user_schedule_schema.schedulable_id.required'),
            'schedulable_id.uuid' => __('request_messages/store_user_schedule_schema.schedulable_id.uuid'),
            'valid_from.required' => __('request_messages/store_user_schedule_schema.valid_from.required'),
            'valid_from.date' => __('request_messages/store_user_schedule_schema.valid_from.date'),
            'valid_through.required' => __('request_messages/store_user_schedule_schema.valid_through.required'),
            'valid_through.date' => __('request_messages/store_user_schedule_schema.valid_through.date'),
            'valid_through.after_or_equal' => __('request_messages/store_user_schedule_schema.valid_through.after_or_equal'),
            'valid_through.before_or_equal' => __('request_messages/store_user_schedule_schema.valid_through.before_or_equal'),
            'days_of_week.required' => __('request_messages/store_user_schedule_schema.days_of_week.required'),
            'days_of_week.array' => __('request_messages/store_user_schedule_schema.days_of_week.array'),
            'days_of_week.*.integer' => __('request_messages/store_user_schedule_schema.days_of_week.*.integer'),
            'clock_in.required' => __('request_messages/store_user_schedule_schema.clock_in.required'),
            'clock_in.string' => __('request_messages/store_user_schedule_schema.clock_in.string'),
            'clock_out.required' => __('request_messages/store_user_schedule_schema.clock_out.required'),
            'clock_out.string' => __('request_messages/store_user_schedule_schema.clock_out.string'),
            'schedule_type_id.required' => __('request_messages/store_user_schedule_schema.schedule_type_id.required'),
            'schedule_type_id.Illuminate\Validation\Rules\Enum' => __('request_messages/store_user_schedule_schema.schedule_type_id.enum'),
        ];
    }
}
