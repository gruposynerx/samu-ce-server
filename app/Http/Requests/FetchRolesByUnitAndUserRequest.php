<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Query parameters
 */
class FetchRolesByUnitAndUserRequest extends FormRequest
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
            'urc_id' => 'required|uuid|exists:urgency_regulation_centers,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'É obrigatório informar o usuário.',
            'user_id.uuid' => 'O usuário é inválido.',
            'user_id.exists' => 'O usuário não existe.',
            'urc_id.required' => 'É obrigatório informar a unidade base do usuário.',
            'urc_id.uuid' => 'A unidade base é inválida.',
            'urc_id.exists' => 'A unidade base não existe.',
        ];
    }
}
