<?php

namespace App\Http\Requests;

use App\Enums\TicketTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Query parameters
 */
class IndexRadioOperationRequest extends FormRequest
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
            'search' => 'nullable|string',
            'filter_by_awaiting_vehicles' => 'nullable|required_without:filter_by_vehicles_sent|boolean|different:filter_by_vehicles_sent',
            'filter_by_vehicles_sent' => 'nullable|required_without:filter_by_awaiting_vehicles|boolean|different:filter_by_awaiting_vehicles',
            'ticket_type_id' => ['nullable', 'integer', Rule::in(TicketTypeEnum::PRIMARY_OCCURRENCE->value, TicketTypeEnum::SECONDARY_OCCURRENCE->value)],
        ];
    }
}
