<?php

namespace App\Http\Requests;

use App\Enums\PeriodTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Query parameters
 */
class IndexUserScheduleRequest extends FormRequest
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
            'regional_groups' => 'nullable|array',
            'regional_groups.*' => 'uuid|exists:regional_groups,id',
            'date' => 'required|date_format:Y-m-d',
            'period_type' => ['required', 'integer', new Enum(PeriodTypeEnum::class)],
            'search' => 'nullable|string',
            'occupation_codes' => 'nullable|array',
            'regional_group_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'regional_groups.*.uuid' => __('request_messages/index_user_schedule.regional_groups.*.uuid'),
            'regional_groups.*.exists' => __('request_messages/index_user_schedule.regional_groups.*.exists'),
            'date.date' => __('request_messages/index_user_schedule.date.date'),
            'date.date_format' => __('request_messages/index_user_schedule.date.date_format'),
            'date.required' => __('request_messages/index_user_schedule.date.required'),
            'period_type.required' => __('request_messages/index_user_schedule.period_type.required'),
            'period_type.integer' => __('request_messages/index_user_schedule.period_type.integer'),
            'period_type.Illuminate\Validation\Rules\Enum' => __('request_messages/index_user_schedule.period_type.Illuminate\Validation\Rules\Enum'),
            'search.string' => __('request_messages/index_user_schedule.search.string'),
        ];
    }
}
