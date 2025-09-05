<?php

namespace App\Http\Requests;

use App\Enums\PeriodTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Query parameters
 */
class IndexUserScheduleReportsRequest extends FormRequest
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
            'search' => 'nullable|string',
            'occupation_codes' => 'nullable|array',
            'regional_group_id' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'professional_ids' => 'nullable|array',
            'professional_ids.*' => 'uuid|exists:users,id',
            'base_ids' => 'nullable|array',
            'base_ids.*' => 'uuid|exists:bases,id',
            'links' => 'nullable|array',
            'event_ids' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'regional_groups.*.uuid' => __('request_messages/index_user_schedule.regional_groups.*.uuid'),
            'regional_groups.*.exists' => __('request_messages/index_user_schedule.regional_groups.*.exists'),
            'search.string' => __('request_messages/index_user_schedule.search.string'),
            'professional_ids.*.uuid' => __('request_messages/index_user_schedule.professional_ids.*.uuid'),
            'professional_ids.*.exists' => __('request_messages/index_user_schedule.professional_ids.*.exists'),
            'base_ids.*.uuid' => __('request_messages/index_user_schedule.base_ids.*.uuid'),
            'base_ids.*.exists' => __('request_messages/index_user_schedule.base_ids.*.exists'),
        ];
    }
}
