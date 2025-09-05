<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrUpdateUserScheduleClocksRequest extends FormRequest
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
            'clock_in' => ['nullable', 'date'],
            'clock_out' => ['nullable', 'date'],
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'user_schedule_id' => ['nullable', 'integer', 'exists:user_schedules,id'],
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
            'clock_in.date' => 'O checkin deve ser uma data.',
            'clock_out.date' => 'O checkout deve ser uma data',
        ];
    }
}