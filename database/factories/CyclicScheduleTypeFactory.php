<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CyclicScheduleType>
 */
class CyclicScheduleTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->name,
            'work_hours' => $this->faker->numberBetween(6, 24),
            'break_hours' => $this->faker->numberBetween(6, 24),
            'is_active' => $this->faker->boolean,
        ];
    }
}
