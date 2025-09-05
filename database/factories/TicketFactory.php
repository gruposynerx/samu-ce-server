<?php

namespace Database\Factories;

use App\Enums\AttendanceStatusEnum;
use App\Enums\GenderCodeEnum;
use App\Enums\TicketTypeEnum;
use App\Enums\TimeUnitEnum;
use App\Models\City;
use App\Models\Patient;
use App\Models\Ticket;
use App\Models\UrgencyRegulationCenter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $multipleVictims = $this->faker->boolean;

        return [
            'requester_id' => RequesterFactory::new(),
            'urc_id' => UrgencyRegulationCenter::first(),
            'created_by' => UserFactory::new(),
            'ticket_type_id' => $this->faker->randomElement(TicketTypeEnum::cases())->value,
            'city_id' => City::inRandomOrder()->first(),
            'multiple_victims' => $multipleVictims,
            'number_of_victims' => $multipleVictims ? $this->faker->numberBetween(2, 10) : 1,
            'ticket_sequence_per_urgency_regulation_center' => Ticket::max('ticket_sequence_per_urgency_regulation_center') + 1,
            'opening_at' => $this->faker->dateTimeBetween('-15 minutes', 'now'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Ticket $ticket) {
            foreach (range(1, $ticket->number_of_victims) as $index) {
                $patient = Patient::create([
                    'name' => $this->faker->firstName,
                    'age' => $this->faker->numberBetween(0, 130),
                    'time_unit_id' => $this->faker->randomElement(TimeUnitEnum::cases())->value,
                    'gender_code' => $this->faker->randomElement(GenderCodeEnum::cases())->value,
                ]);

                $attendance = match (TicketTypeEnum::tryFrom($ticket->ticket_type_id)) {
                    TicketTypeEnum::PRIMARY_OCCURRENCE => PrimaryAttendanceFactory::new()->create(),
                    TicketTypeEnum::SECONDARY_OCCURRENCE => SecondaryAttendanceFactory::new()->create(),
                    TicketTypeEnum::INFORMATION => OtherAttendanceFactory::new()->create(),
                    TicketTypeEnum::MISTAKE => OtherAttendanceFactory::new()->create(),
                    TicketTypeEnum::PRANK_CALL => OtherAttendanceFactory::new()->create(),
                    TicketTypeEnum::CALL_DROP => OtherAttendanceFactory::new()->create(),
                    TicketTypeEnum::CONTACT_WITH_SAMU_TEAM => OtherAttendanceFactory::new()->create(),
                    default => null,
                };

                $isOtherAttendance = in_array($ticket->ticket_type_id,
                    array_column(TicketTypeEnum::OTHER_ATTENDANCES, 'value')
                );

                $attendance->attendable()->create([
                    'urc_id' => $ticket->urc_id,
                    'patient_id' => $patient->id,
                    'created_by' => auth()->user()->id ?? User::first()->id,
                    'ticket_id' => $ticket->id,
                    'attendance_status_id' => $isOtherAttendance
                        ? AttendanceStatusEnum::COMPLETED->value
                        : AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION->value,
                ]);
            }
        });
    }
}
