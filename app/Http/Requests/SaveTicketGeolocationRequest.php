<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveTicketGeolocationRequest extends FormRequest
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
            'geolocation' => ['sometimes', 'nullable', 'array'],
            'geolocation.address' => ['sometimes', 'nullable', 'array'],
            'geolocation.address.street' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.state' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.city' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.neighborhood' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.postal_code' => ['sometimes', 'nullable', 'string'],
            'geolocation.address.street_number' => ['sometimes', 'nullable', 'string'],
            'geolocation.place_id' => ['sometimes', 'nullable', 'string'],
            'geolocation.location' => ['sometimes', 'nullable', 'array'],
            'geolocation.location.lat' => ['sometimes', 'nullable', 'numeric'],
            'geolocation.location.lng' => ['sometimes', 'nullable', 'numeric'],
            'geolocation.formatted_address' => ['sometimes', 'nullable', 'string'],
            'geolocation.viewport' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
