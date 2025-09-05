<?php

namespace Tests\RequestFactories;

use App\Enums\GenderCodeEnum;
use App\Enums\ResourceEnum;
use App\Enums\TicketTypeEnum;
use App\Enums\TimeUnitEnum;
use App\Enums\TransferReasonEnum;
use App\Models\City;
use App\Models\Unit;
use Worksome\RequestFactories\RequestFactory;

class StoreSecondaryAttendanceRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        $unitOriginOrDestinationId = Unit::factory()->create()->id;

        return [
            'ticket_type_id' => TicketTypeEnum::SECONDARY_OCCURRENCE,
            'opening_at' => now()->subMinutes(5)->startOfSecond(),
            'city_id' => City::inRandomOrder()->first()->id,
            'requester' => array_merge(
                [
                    'name' => $this->faker->firstName,
                    'primary_phone' => $this->faker->numerify('(##) #####-####'),
                    'secondary_phone' => $this->faker->numerify('(##) #####-####'),
                ],
                $this->attributes['requester'] ?? [],
            ),
            'patients' => [
                [
                    'name' => $this->faker->name,
                    'age' => $this->faker->numberBetween(1, 100),
                    'time_unit_id' => $this->faker->randomElement(TimeUnitEnum::cases())->value,
                    'gender_code' => $this->faker->randomElement(GenderCodeEnum::cases())->value,
                ],
            ],
            'observations' => $this->faker->sentence,
            'transfer_reason_id' => $this->faker->randomElement(TransferReasonEnum::TRANSFER_REASON_SECONDARY_ATTENDANCE)->value,
            'in_central_bed' => $this->faker->boolean,
            'protocol' => $this->faker->numerify('######'),
            'diagnostic_hypothesis' => $this->faker->sentence,
            'complement_origin' => $this->faker->sentence,
            'complement_destination' => $this->faker->sentence,
            'requested_resource_id' => $this->faker->randomElement(ResourceEnum::cases())->value,
            'transfer_observation' => $this->faker->sentence,
            'unit_origin_id' => $unitOriginOrDestinationId,
            'unit_destination_id' => $unitOriginOrDestinationId,
        ];
    }
}
