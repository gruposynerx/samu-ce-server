<?php

namespace Database\Factories;

use App\Enums\ResourceEnum;
use App\Enums\TransferReasonEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SecondaryAttendance>
 */
class SecondaryAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transfer_reason_id' => $this->faker->randomElement(TransferReasonEnum::TRANSFER_REASON_SECONDARY_ATTENDANCE)->value,
            'in_central_bed' => $this->faker->boolean,
            'protocol' => $this->faker->numerify('######'),
            'diagnostic_hypothesis' => $this->faker->sentence,
            'complement_origin' => $this->faker->sentence,
            'complement_destination' => $this->faker->sentence,
            'requested_resource_id' => $this->faker->randomElement(ResourceEnum::cases())->value,
            'transfer_observation' => $this->faker->sentence,
            'unit_origin_id' => UnitFactory::new(),
            'unit_destination_id' => UnitFactory::new(),
        ];
    }
}
