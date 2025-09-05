<?php

namespace Database\Factories;

use App\Enums\BaseTypeEnum;
use App\Enums\VehicleTypeEnum;
use App\Models\City;
use App\Models\UrgencyRegulationCenter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Base>
 */
class BaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseTypes = Arr::pluck(BaseTypeEnum::cases(), 'value');

        return [
            'name' => $this->faker->name,
            'national_health_registration' => fake()->numerify('###########'),
            'street' => $this->faker->streetName,
            'house_number' => $this->faker->buildingNumber,
            'zip_code' => fake()->numerify('########'),
            'neighborhood' => $this->faker->word,
            'complement' => $this->faker->word,
            'city_id' => City::first(),
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'telephone' => fake()->numerify('###########'),
            'company_registration_number' => fake()->numerify('###########'),
            'unit_type_id' => Arr::random($baseTypes),
            'company_name' => $this->faker->company,
            'urc_id' => UrgencyRegulationCenter::first(),
            'is_active' => $this->faker->boolean,
            'vehicle_type_id' => Arr::random(VehicleTypeEnum::cases()),
        ];
    }
}
