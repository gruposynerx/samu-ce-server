<?php

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class CyclicScheduleTypesRequestFactory extends RequestFactory
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
            'work_hours' => $this->faker->numberBetween(6, 24),
            'break_hours' => $this->faker->numberBetween(6, 24),
        ];
    }
}
