<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePowerBIReportRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('power_bi_reports', 'name')->where('urc_id', auth()->user()->urc_id),
            ],
            'url' => 'required|url',
            'roles' => 'required|array|min:1',
            'roles.*' => 'required|uuid|exists:roles,id',
            'description' => 'nullable|string',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'url' => 'URL',
            'description' => 'descrição',
            'roles' => 'perfis',
        ];
    }
}
