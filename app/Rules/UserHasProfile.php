<?php

namespace App\Rules;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;

class UserHasProfile implements ValidationRule
{
    public function __construct(private array $profile)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = User::find($value);

        $profileNames = array_map(fn ($profile) => UserRoleEnum::tryFrom($profile)->message(), $this->profile);

        $formattedProfileNames = Arr::join($profileNames, ', ', ' e ');

        $hasProfiles = $user->whereHas('roles', function ($query) {
            $query->whereIn('name', $this->profile);
        })->exists();

        if (!$hasProfiles) {
            $fail("O usuário {$user->name} não possui perfil {$formattedProfileNames}.");
        }
    }
}
