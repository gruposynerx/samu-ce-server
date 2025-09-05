<?php

namespace Database\Factories;

use App\Enums\RequesterTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Requester>
 */
class RequesterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requester_type_id' => $this->faker->randomElement(RequesterTypeEnum::cases()),
            'name' => $this->faker->name,
            'primary_phone' => $this->faker->numerify('(##) 9#####-####'),
            'secondary_phone' => $this->faker->numerify('(##) 9#####-####'),
            //'identifier' => $this->faker->numerify('###########') não está implementado, mas existe no banco
            'council_number' => $this->faker->numerify('###########'),
        ];
    }
}
