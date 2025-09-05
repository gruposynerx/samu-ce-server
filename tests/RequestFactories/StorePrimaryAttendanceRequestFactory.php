<?php

namespace Tests\RequestFactories;

use App\Enums\DistanceTypeEnum;
use App\Enums\GenderCodeEnum;
use App\Enums\LocationTypeEnum;
use App\Enums\TicketTypeEnum;
use App\Enums\TimeUnitEnum;
use Worksome\RequestFactories\RequestFactory;

class StorePrimaryAttendanceRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        $multipleVictims = $this->faker->boolean;
        $totalPatients = $multipleVictims ? $this->faker->numberBetween(2, 10) : 1;

        $patients = collect(range(1, $totalPatients))->map(function () {
            return [
                'name' => $this->faker->name,
                'age' => $this->faker->numberBetween(1, 100),
                'time_unit_id' => $this->faker->randomElement(TimeUnitEnum::cases())->value,
                'gender_code' => $this->faker->randomElement(GenderCodeEnum::cases())->value,
            ];
        });

        return [
            'ticket_type_id' => TicketTypeEnum::PRIMARY_OCCURRENCE,
            'opening_at' => now()->subMinutes(5)->startOfSecond(),
            'city_id' => $this->faker->numberBetween(1, 5570),
            'multiple_victims' => $multipleVictims,
            'number_of_victims' => count($patients),
            'requester' => array_merge(
                [
                    'name' => $this->faker->firstName,
                    'primary_phone' => $this->faker->numerify('(##) #####-####'),
                    'secondary_phone' => $this->faker->numerify('(##) #####-####'),
                ],
                $this->attributes['requester'] ?? [],
            ),
            'patients' => $patients,
            'street' => $this->faker->streetName,
            'house_number' => $this->faker->buildingNumber,
            'neighborhood' => $this->faker->city,
            'primary_complaint' => $this->faker->sentence,
            'reference_place' => $this->faker->sentence,
            'observations' => $this->faker->sentence,
            'distance_type_id' => $this->faker->randomElement(DistanceTypeEnum::cases())->value,
            'location_type_id' => $this->faker->randomElement(LocationTypeEnum::cases())->value,
        ];
    }
}
