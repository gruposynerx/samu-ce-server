<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatusEnum;
use App\Enums\RolesEnum;
use App\Enums\TicketTypeEnum;
use App\Models\Attendance;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\RequestFactories\StoreOtherAttendancesRequestFactory;
use Tests\TestCase;

class OtherAttendanceTest extends TestCase
{
    use DatabaseTransactions;

    public static function availableRoles()
    {
        $roles = RolesEnum::cases();

        foreach ($roles as $role) {
            yield "{$role->value}" => [
                $role->value,
                in_array($role->value, [RolesEnum::SUPER_ADMIN->value, RolesEnum::ADMIN->value, RolesEnum::TARM->value, RolesEnum::MEDIC->value, RolesEnum::RADIO_OPERATOR->value, RolesEnum::TEAM_LEADER->value]),
            ];
        }
    }

    public function test_user_can_create_other_attendance_with_valid_data(): void
    {
        Sanctum::actingAs($this->superAdminUser);

        $lastTicket = Ticket::factory()
            ->recycle($this->superAdminUser->currentUrgencyRegulationCenter)
            ->create([
                'ticket_type_id' => Arr::random(TicketTypeEnum::OTHER_ATTENDANCES)->value,
            ]);

        $payload = StoreOtherAttendancesRequestFactory::new()->create();

        $response = $this->postJson('/api/ticket/other-attendance', $payload);

        $response->assertOk();

        $parsedOpeningAt = Carbon::parse($payload['opening_at'], 'America/Fortaleza')->timezone('UTC')->format('Y-m-d H:i:s.u');
        $ticketData = array_merge(
            Arr::only($payload, ['ticket_type_id', 'city_id', 'opening_at']),
            [
                'ticket_sequence_per_urgency_regulation_center' => $lastTicket->ticket_sequence_per_urgency_regulation_center + 1,
                'opening_at' => $parsedOpeningAt,
            ]
        );

        $otherAttendanceData = Arr::except($payload, ['ticket_type_id', 'patients', 'requester', 'opening_at', 'city_id']);
        $ticketData['opening_at'] = Carbon::parse($ticketData['opening_at'])->format('Y-m-d H:i:s');
        $requesterData = $payload['requester'];
        $attendance = Attendance::where('ticket_id', $lastTicket->id)->first();

        $attendanceData = [
            'id' => $attendance->id,
            'ticket_id' => $lastTicket->id,
            'attendable_id' => $attendance->attendable_id,
            'created_by' => $this->superAdminUser->id,
            'attendance_status_id' => AttendanceStatusEnum::COMPLETED->value,
        ];

        $attendanceLogData = [
            'attendance_id' => $attendance->id,
            'user_id' => $this->superAdminUser->id,
            'current_attendance_status_id' => AttendanceStatusEnum::COMPLETED->value,
            'previous_attendance_status_id' => null,
        ];

        $this->assertDatabaseHas('other_attendances', $otherAttendanceData);
        $this->assertDatabaseHas('tickets', $ticketData);
        $this->assertDatabaseHas('requesters', $requesterData);
        $this->assertDatabaseHas('attendances', $attendanceData);
        $this->assertDatabaseHas('attendance_logs', $attendanceLogData);
    }

    #[DataProvider('invalidPayloads')]
    public function test_create_other_attendance_rule($invalidPayload, $error)
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->postJson('/api/ticket/other-attendance', StoreOtherAttendancesRequestFactory::new($invalidPayload())->create());

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($error());
    }

    public static function invalidPayloads()
    {
        yield 'ticket_type_id is required' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['ticket_type_id' => null]),
            fn () => ['ticket_type_id' => __('request_messages/store_other_attendance.ticket_type_id.required')],
        ];

        yield 'ticket_type_id is numeric' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['ticket_type_id' => 'non numeric']),
            fn () => ['ticket_type_id' => __('request_messages/store_other_attendance.ticket_type_id.numeric')],
        ];

        yield 'ticket_type_id is other attendances' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['ticket_type_id' => TicketTypeEnum::OTHER_ATTENDANCES]),
            fn () => ['ticket_type_id' => __('request_messages/store_other_attendance.ticket_type_id.in')],
        ];

        yield 'opening_at is required' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['opening_at' => null]),
            fn () => ['opening_at' => __('request_messages/store_other_attendance.opening_at.required')],
        ];

        yield 'opening_at must be a date' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['opening_at' => 'not a date']),
            fn () => ['opening_at' => __('request_messages/store_other_attendance.opening_at.date')],
        ];

        yield 'city_id is numeric' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['city_id' => 'not a number']),
            fn () => ['city_id' => __('request_messages/store_other_attendance.city_id.numeric')],
        ];

        yield 'city_id is a valid id' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['city_id' => 99999]),
            fn () => ['city_id' => __('request_messages/store_other_attendance.city_id.exists')],
        ];

        yield 'requester.name is required' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['requester' => ['name' => null]]),
            fn () => ['requester.name' => __('request_messages/store_other_attendance.requester.name.required')],
        ];

        yield 'requester.name is string' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['requester' => ['name' => 1234567890]]),
            fn () => ['requester.name' => __('request_messages/store_other_attendance.requester.name.string')],
        ];

        yield 'requester.primary_phone is string' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['requester' => ['primary_phone' => 1234567890]]),
            fn () => ['requester.primary_phone' => __('request_messages/store_other_attendance.requester.primary_phone.string')],
        ];

        yield 'requester.secondary_phone is string' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['requester' => ['secondary_phone' => 1234567890]]),
            fn () => ['requester.secondary_phone' => __('request_messages/store_other_attendance.requester.secondary_phone.string')],
        ];

        yield 'patients.name is string' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['patients' => [['name' => 1234567890]]]),
            fn () => ['patients.0.name' => __('request_messages/store_other_attendance.patients.name.string')],
        ];

        yield 'patients.age is integer' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['patients' => [['age' => 'not an integer']]]),
            fn () => ['patients.0.age' => __('request_messages/store_other_attendance.patients.age.integer')],
        ];

        yield 'patients.time_unit_id is integer' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['patients' => [['time_unit_id' => 'not an integer']]]),
            fn () => ['patients.0.time_unit_id' => __('request_messages/store_other_attendance.patients.time_unit_id.integer')],
        ];

        yield 'description is string' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['description' => 1234567890]),
            fn () => ['description' => __('request_messages/store_other_attendance.description.string')],
        ];

        yield 'description is max 3000 characters' => [
            fn () => StoreOtherAttendancesRequestFactory::new(['description' => str_repeat('a', 3001)]),
            fn () => ['description' => __('request_messages/store_other_attendance.description.max')],
        ];
    }
}
