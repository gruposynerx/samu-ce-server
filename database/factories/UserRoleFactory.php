<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\UrgencyRegulationCenter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserRole>
 */
class UserRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roleName = $this->faker->slug;

        return [
            'user_id' => UserFactory::new(),
            'role_id' => Role::create(['name' => $roleName])->id,
            'urc_id' => UrgencyRegulationCenter::first(),
        ];
    }
}
