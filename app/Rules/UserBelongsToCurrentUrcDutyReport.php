<?php

namespace App\Rules;

use App\Enums\DutyReportTypeEnum;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserBelongsToCurrentUrcDutyReport implements ValidationRule
{
    public function __construct(private readonly ?int $dutyReportTypeId)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->dutyReportTypeId === DutyReportTypeEnum::FLEET_MANAGER->value) {
            $user = User::find($value)->whereHas('urgencyRegulationCenters', function ($query) {
                $query->where('urgency_regulation_centers.id', auth()->user()->urc_id);
            })->exists();

            if (!$user) {
                $fail('O usuário não pertence a central de regulação de urgência.');
            }
        }
    }
}
