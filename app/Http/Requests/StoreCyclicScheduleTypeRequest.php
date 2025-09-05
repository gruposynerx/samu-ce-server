<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCyclicScheduleTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', Rule::unique('cyclic_schedule_types', 'name')],
            'work_hours' => ['required', 'integer',
                Rule::unique('cyclic_schedule_types', 'work_hours')
                    ->when(is_int($this->input('break_hours')), function ($query) {
                        return $query->where('break_hours', $this->input('break_hours'));
                    }),
            ],
            'break_hours' => ['required', 'integer',
                Rule::unique('cyclic_schedule_types', 'break_hours')
                    ->when(is_int($this->input('work_hours')), function ($query) {
                        return $query->where('work_hours', $this->input('work_hours'));
                    }),
            ],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('request_messages/store_cyclic_schedule_type.name.required'),
            'name.string' => __('request_messages/store_cyclic_schedule_type.name.string'),
            'name.max' => __('request_messages/store_cyclic_schedule_type.name.max'),
            'name.unique' => __('request_messages/store_cyclic_schedule_type.name.unique'),
            'work_hours.required' => __('request_messages/store_cyclic_schedule_type.work_hours.required'),
            'work_hours.integer' => __('request_messages/store_cyclic_schedule_type.work_hours.integer'),
            'work_hours.unique' => __('request_messages/store_cyclic_schedule_type.work_hours.unique'),
            'break_hours.required' => __('request_messages/store_cyclic_schedule_type.break_hours.required'),
            'break_hours.integer' => __('request_messages/store_cyclic_schedule_type.break_hours.integer'),
            'break_hours.unique' => __('request_messages/store_cyclic_schedule_type.break_hours.unique'),
        ];
    }
}
