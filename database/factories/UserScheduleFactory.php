<?php

namespace Database\Factories;

use App\Models\UserScheduleSchema;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserSchedule>
 */
class UserScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $schema = UserScheduleSchema::factory()->create();
        $fakeDate = $this->faker->dateTimeBetween('now', '+3 months')->format('Y-m-d');

        return [
            'user_id' => $schema->user->id,
            'schema_id' => $schema->id,
            'starts_at' => Carbon::parse("$fakeDate $schema->clock_in"),
            'ends_at' => Carbon::parse("$fakeDate $schema->clock_out"),
            'occupation_code' => $schema->user->cbo,
        ];
    }
}
