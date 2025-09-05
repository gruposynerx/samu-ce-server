<?php

namespace Tests\RequestFactories;

use App\Enums\BaseTypeEnum;
use App\Enums\VehicleTypeEnum;
use App\Models\City;
use App\Models\UrgencyRegulationCenter;
use Worksome\RequestFactories\RequestFactory;

class BaseRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'urc_id' => UrgencyRegulationCenter::inRandomOrder()->first()->id,
            'unit_type_id' => BaseTypeEnum::MOBILE_PRE_HOSPITAL_EMERGENCY_UNIT,
            'city_id' => City::inRandomOrder()->first()->id,
            'name' => $this->faker->company,
            'national_health_registration' => $this->faker->numerify('#######'),
            'street' => $this->faker->streetName,
            'house_number' => $this->faker->buildingNumber,
            'zip_code' => $this->faker->postcode,
            'neighborhood' => $this->faker->streetName,
            'complement' => $this->faker->streetName,
            'latitude' => (string) $this->faker->latitude,
            'longitude' => (string) $this->faker->longitude,
            'telephone' => $this->faker->numerify('(##) #####-####'),
            'company_registration_number' => $this->faker->numerify('########0001##'),
            'company_name' => $this->faker->company,
            'vehicle_type_id' => $this->faker->randomElement(VehicleTypeEnum::cases())->value,
        ];
    }
}
