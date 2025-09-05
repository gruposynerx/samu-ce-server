<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCyclicScheduleTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100', Rule::unique('cyclic_schedule_types', 'name')->ignore($this->route('id'))],
            'work_hours' => ['required', 'integer', Rule::unique('cyclic_schedule_types', 'work_hours')->where('break_hours', $this->input('break_hours'))->ignore($this->route('id'))],
            'break_hours' => ['required', 'integer', Rule::unique('cyclic_schedule_types', 'break_hours')->where('work_hours', $this->input('work_hours'))->ignore($this->route('id'))],
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
            'work_hours.integer' => __('request_messages/store_cyclic_schedule_type.work_hours.integer'),
            'work_hours.unique' => __('request_messages/store_cyclic_schedule_type.work_hours.unique'),
            'break_hours.integer' => __('request_messages/store_cyclic_schedule_type.break_hours.integer'),
            'break_hours.unique' => __('request_messages/store_cyclic_schedule_type.break_hours.unique'),
        ];
    }
}
