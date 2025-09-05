<?php

namespace App\Rules;

use App\Models\Vehicle;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class OccupeVehicleType implements DataAwareRule, ValidationRule
{
    protected array $data;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $fleets = $this->data['radio_operation_fleet'];

        foreach ($fleets as $fleet) {
            if (!isset($fleet['vehicle_id'])) {
                throw ValidationException::withMessages([$attribute => 'A viatura é obrigatória.']);
            }

            $vehicle = Vehicle::find($fleet['vehicle_id']);
            $ableOccupations = $vehicle->ableOccupations;
            $ableOccupationsArr = $ableOccupations->pluck('occupation_id');

            $userOccupations = collect(Arr::map($fleet['users'], function ($user) {
                if (str_contains($user['occupation_id'], '2251')) {
                    return '2251';
                }

                return $user['occupation_id'];
            }));

            $diff = $ableOccupationsArr->diff($userOccupations);

            $optionals = $ableOccupations->where('required', false);
            $optionalAmount = $ableOccupations
                ->whereIn('occupation_id', $userOccupations)
                ->where('required', false)
                ->count();

            $requiredOccupations = $ableOccupations
                ->whereIn('occupation_id', $diff)
                ->where('required', true);

            $vehicleDescription = "{$vehicle->code}/{$vehicle->license_plate}";

            if ($requiredOccupations->count() > 0) {
                $fail("O veículo {$vehicleDescription} precisa ser ocupado pelos CBOs {$requiredOccupations->implode('occupation_id', ', ')}");
            }

            if ($optionals->count() > 0 && $optionalAmount === 0) {
                $fail("O veículo {$vehicleDescription} precisa ser ocupado por pelo menos um dos CBOs: {$optionals->implode('occupation_id', ', ')}");
            }
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
