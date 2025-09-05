<?php

namespace Database\Factories;

use App\Models\UrgencyRegulationCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoordinationNote>
 */
class CoordinationNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'note' => $this->faker->text(),
            'urc_id' => UrgencyRegulationCenter::first(),
        ];
    }
}
