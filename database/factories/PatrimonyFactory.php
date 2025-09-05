<?php

namespace Database\Factories;

use App\Enums\PatrimonyStatusEnum;
use App\Enums\PatrimonyTypeEnum;
use App\Models\UrgencyRegulationCenter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patrimony>
 */
class PatrimonyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patrimony_type_id' => Arr::random(PatrimonyTypeEnum::cases())->value,
            'identifier' => $this->faker->numerify('######'),
            'patrimony_status_id' => Arr::random(PatrimonyStatusEnum::cases())->value,
            'vehicle_id' => VehicleFactory::new(),
            'urc_id' => UrgencyRegulationCenter::first(),
        ];
    }
}
