<?php

namespace App\Http\Requests;

use App\Enums\PlaceStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class IndexPlaceManagementRequest extends FormRequest
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
            'search' => ['nullable', 'string'],
            'place_statuses' => ['nullable', 'array'],
            'place_statuses.*' => new Enum(PlaceStatusEnum::class),
        ];
    }

    public function attributes(): array
    {
        return [
            'search' => 'busca',
            'place_statuses' => 'status dos locais',
        ];
    }
}
