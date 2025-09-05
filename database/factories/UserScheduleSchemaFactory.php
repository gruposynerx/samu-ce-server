<?php

namespace Database\Factories;

use App\Enums\ScheduleTypeEnum;
use App\Models\Base;
use App\Models\RegionalGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserScheduleSchema>
 */
class UserScheduleSchemaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::factory()->create();
        $base = Base::factory()->create([
            'regional_group_id' => RegionalGroup::factory()->create()->id,
        ]);

        return [
            'user_id' => $user->id,
            'schedulable_id' => $base->id,
            'schedulable_type' => app(Base::class)->getMorphClass(),
            'days_of_week' => $this->faker->randomElements(range(0, 6), $this->faker->numberBetween(1, 7)),
            'valid_from' => $this->faker->dateTimeBetween('now', '+1 weeks')->format('Y-m-d'),
            'valid_through' => $this->faker->dateTimeBetween('+1 weeks', '+2 weeks')->format('Y-m-d'),
            'clock_in' => strval($this->faker->dateTimeBetween('today 08:00', 'today 16:00')->format('H:i')),
            'clock_out' => strval($this->faker->dateTimeBetween('today 16:00', 'today 23:00')->format('H:i')),
            'schedule_type_id' => $this->faker->randomElement(ScheduleTypeEnum::cases())->value,
        ];
    }
}
