<?php

namespace Tests\RequestFactories;

use App\Enums\GenderCodeEnum;
use App\Enums\TicketTypeEnum;
use App\Enums\TimeUnitEnum;
use App\Models\City;
use Worksome\RequestFactories\RequestFactory;

class StoreOtherAttendancesRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'requester' => array_merge(
                [
                    'name' => $this->faker->firstName,
                    'primary_phone' => $this->faker->numerify('(##) #####-####'),
                    'secondary_phone' => $this->faker->numerify('(##) #####-####'),
                ],
                $this->attributes['requester'] ?? [],
            ),
            'city_id' => City::inRandomOrder()->first()->id,
            'patients' => array_merge(
                [
                    'name' => $this->faker->firstName,
                    'age' => $this->faker->numberBetween(1, 100),
                    'time_unit_id' => $this->faker->randomElement(TimeUnitEnum::cases())->value,
                    'gender_code' => $this->faker->randomElement(GenderCodeEnum::cases())->value,
                ],
                $this->attributes['requester'] ?? [],
            ),
            'description' => $this->faker->sentence,
            'opening_at' => now()->subMinutes(5)->startOfSecond(),
            'ticket_type_id' => $this->faker->randomElement(TicketTypeEnum::OTHER_ATTENDANCES)->value,
        ];
    }
}
