<?php

namespace Database\Factories;

use App\Enums\DistanceTypeEnum;
use App\Enums\LocationTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PrimaryAttendance>
 */
class PrimaryAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'street' => $this->faker->streetName,
            'house_number' => $this->faker->buildingNumber,
            'neighborhood' => $this->faker->streetName,
            'reference_place' => $this->faker->streetName(3),
            'primary_complaint' => $this->faker->sentence,
            'observations' => $this->faker->sentence,
            'distance_type_id' => $this->faker->randomElement(DistanceTypeEnum::cases())->value,
            'location_type_id' => $this->faker->randomElement(LocationTypeEnum::cases())->value,
            'unit_destination_id' => UnitFactory::new(),
        ];
    }
}
