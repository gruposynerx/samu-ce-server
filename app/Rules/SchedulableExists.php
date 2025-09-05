<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class SchedulableExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $existsInBases = DB::table('bases')->where('id', $value)->exists();
        $existsInUrgencyRegulationCenters = DB::table('urgency_regulation_centers')->where('id', $value)->exists();

        if (!$existsInBases && !$existsInUrgencyRegulationCenters) {
            $fail('O local de trabalho não existe.');
        }
    }
}
