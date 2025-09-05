<?php

namespace Tests\RequestFactories;

use App\Enums\UnitTypeEnum;
use App\Models\City;
use Worksome\RequestFactories\RequestFactory;

class UnitRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'city_id' => City::inRandomOrder()->first()->id,
            'national_health_registration' => strval($this->faker->randomNumber(6)),
            'unit_type_id' => $this->faker->randomElement(UnitTypeEnum::cases())->value,
            'street' => $this->faker->streetName,
            'house_number' => $this->faker->buildingNumber,
            'zip_code' => $this->faker->postcode,
            'neighborhood' => $this->faker->streetName,
            'complement' => $this->faker->streetName,
            'latitude' => strval($this->faker->latitude),
            'longitude' => strval($this->faker->longitude),
            'telephone' => $this->faker->numerify('###########'),
            'company_registration_number' => $this->faker->numerify('###########'),
            'company_name' => $this->faker->company,
        ];
    }
}
