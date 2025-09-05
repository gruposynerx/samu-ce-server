<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => City::first(),
            'identifier' => Str::random(10),
            'national_health_card' => Str::random(10),
            'birthdate' => fake()->date(),
            'gender_code' => fake()->randomElement(['M', 'F', 'O']),
            'phone' => fake()->numerify('###########'),
            'whatsapp' => fake()->numerify('###########'),
            'neighborhood' => fake()->word(),
            'street_type' => fake()->randomDigitNotZero(),
            'street' => fake()->word(),
            'house_number' => fake()->randomNumber(2),
            'complement' => fake()->word(),
            'council_number' => fake()->randomNumber(2),
            'cbo' => fake()->randomNumber(5),
            'is_active' => fake()->boolean(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
