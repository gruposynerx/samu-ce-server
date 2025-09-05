<?php

namespace App\Rules;

use App\Models\Occupation;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FleetOccupationExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== '2251') {
            $isValid = Occupation::where('code', $value)
                ->exists();

            if (!$isValid) {
                $fail("O CBO {$value} não existe.");
            }
        }
    }
}
