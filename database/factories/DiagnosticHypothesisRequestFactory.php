<?php

namespace Database\Factories;

use App\Enums\NatureTypeEnum;
use Worksome\RequestFactories\RequestFactory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DiagnosticHypothesisRequestFactory extends RequestFactory
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
            'nature_types_id' => $this->faker->randomElements(NatureTypeEnum::cases(), $this->faker->numberBetween(1, 7)),
        ];
    }
}
