<?php

namespace Database\Factories;

use App\Enums\UnitTypeEnum;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'city_id' => City::inRandomOrder()->first(),
            'national_health_registration' => $this->faker->randomNumber(6),
            'unit_type_id' => $this->faker->randomElement(UnitTypeEnum::class)->value,
            'street' => $this->faker->streetName,
            'house_number' => $this->faker->buildingNumber,
            'zip_code' => $this->faker->postcode,
            'neighborhood' => $this->faker->streetName,
            'complement' => $this->faker->streetName,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'telephone' => $this->faker->numerify('###########'),
            'company_registration_number' => $this->faker->numerify('###########'),
            'company_name' => $this->faker->company,
            'is_active' => $this->faker->boolean,
        ];
    }
}
