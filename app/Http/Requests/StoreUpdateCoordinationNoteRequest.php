<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUpdateCoordinationNoteRequest extends FormRequest
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
            'note' => [
                'required',
                'string',
                'max:2000',
                Rule::unique('coordination_notes', 'note')->where('urc_id', auth()->user()->urc_id),
            ],
        ];
    }

    public function messages()
    {
        return [
            'note.required' => 'O recado é obrigatória.',
            'note.unique' => 'O recado já existe.',
            'note.max' => 'O recado não deve ter mais que 2000 caracteres.',
        ];
    }
}
