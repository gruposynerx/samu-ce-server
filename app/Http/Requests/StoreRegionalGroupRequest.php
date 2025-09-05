<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegionalGroupRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:200', 'unique:regional_groups,name'],
            'bases' => ['required', 'array'],
            'bases.*' => [
                'uuid',
                Rule::exists('bases', 'id')->whereNull('regional_group_id'),
            ],
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
            'name.required' => 'O nome do grupo regional é obrigatório.',
            'name.string' => 'O nome do grupo regional deve ser uma string.',
            'name.max' => 'O nome deve ter no máximo 200 caracteres.',
            'name.unique' => 'Nome do grupo regional cadastrado já existe.',
            'bases.required' => 'O grupo regional deve ter pelo menos uma base vinculada.',
            'bases.array' => 'Bases deve ser uma array.',
            'bases.exists' => 'A base selecionada já está em um grupo regional.',
        ];
    }
}
