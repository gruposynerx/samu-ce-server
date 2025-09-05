<?php

namespace Database\Factories;

use App\Enums\VehicleTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_type_id' => Arr::random(VehicleTypeEnum::cases())->value,
            'code' => $this->faker->numerify('######'),
            'license_plate' => $this->faker->bothify('???###'),
            'base_id' => BaseFactory::new(),
            'chassis' => Str::random(17),
        ];
    }
}
