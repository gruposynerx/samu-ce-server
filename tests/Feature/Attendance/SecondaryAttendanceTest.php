<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatusEnum;
use App\Enums\RequesterTypeEnum;
use App\Enums\RolesEnum;
use App\Enums\TicketTypeEnum;
use App\Events\RefreshAttendance\RefreshRadioOperation;
use App\Events\RefreshAttendance\RefreshSecondaryAttendance;
use App\Models\Attendance;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Tests\RequestFactories\StoreSecondaryAttendanceRequestFactory;
use Tests\TestCase;

class SecondaryAttendanceTest extends TestCase
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

    public function test_user_can_create_secondary_attendance_with_valid_data()
    {
        Sanctum::actingAs($this->superAdminUser);

        $lastTicket = Ticket::factory()
            ->recycle($this->superAdminUser->currentUrgencyRegulationCenter)
            ->create([
                'ticket_type_id' => TicketTypeEnum::SECONDARY_OCCURRENCE->value,
            ]);

        Event::fake([
            RefreshSecondaryAttendance::class,
            RefreshRadioOperation::class,
        ]);

        $payload = StoreSecondaryAttendanceRequestFactory::new()->create();

        $response = $this->postJson('/api/ticket/secondary-attendance', $payload);
        $response->assertOk();

        $parsedOpeningAt = Carbon::parse($payload['opening_at'], 'America/Fortaleza')->timezone('UTC')->format('Y-m-d H:i:s.u');
        $secondaryAttendanceData = Arr::except($payload, ['ticket_type_id', 'patients', 'requester', 'opening_at', 'city_id']);
        $ticketData = array_merge(
            Arr::only($payload, ['ticket_type_id', 'city_id', 'opening_at']),
            [
                'ticket_sequence_per_urgency_regulation_center' => $lastTicket->ticket_sequence_per_urgency_regulation_center + 1,
                'opening_at' => $parsedOpeningAt,
            ]
        );
        $ticketData['opening_at'] = Carbon::parse($ticketData['opening_at'])->format('Y-m-d H:i:s');
        $requesterData = $payload['requester'];

        $attendance = Attendance::where('ticket_id', $lastTicket->id)->first();

        $attendanceData = [
            'id' => $attendance->id,
            'ticket_id' => $lastTicket->id,
            'attendable_id' => $attendance->attendable_id,
            'created_by' => $this->superAdminUser->id,
            'attendance_status_id' => AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION->value,
        ];

        $attendanceLogData = [
            'attendance_id' => $attendance->id,
            'user_id' => $this->superAdminUser->id,
            'current_attendance_status_id' => AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION->value,
            'previous_attendance_status_id' => null,
        ];

        $this->assertDatabaseHas('secondary_attendances', $secondaryAttendanceData);
        $this->assertDatabaseHas('attendances', $attendanceData);
        $this->assertDatabaseHas('tickets', $ticketData);
        $this->assertDatabaseHas('requesters', $requesterData);
        $this->assertDatabaseHas('attendance_logs', $attendanceLogData);

        Event::assertDispatched(RefreshSecondaryAttendance::class);
        Event::assertDispatched(RefreshRadioOperation::class);
    }

    #[DataProvider('invalidPayloads')]
    public function test_create_secondary_attendance_rule($invalidPayload, $error)
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->postJson('/api/ticket/secondary-attendance', StoreSecondaryAttendanceRequestFactory::new($invalidPayload())->create());

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($error());
    }

    public function test_user_can_show_data_from_recently_created_secondary_attendance()
    {
        Sanctum::actingAs($this->superAdminUser);

        $ticket = Ticket::factory()
            ->recycle($this->superAdminUser->currentUrgencyRegulationCenter)
            ->create([
                'ticket_type_id' => TicketTypeEnum::SECONDARY_OCCURRENCE->value,
                'number_of_victims' => 1,
            ]);
        $attendance = $ticket->attendances()->first();

        $response = $this->getJson("/api/ticket/secondary-attendance/{$attendance->id}");

        $secondaryAttendanceKeys = [
            'id',
            'transfer_reason_id',
            'in_central_bed',
            'protocol',
            'diagnostic_hypothesis',
            'complement_origin',
            'complement_destination',
            'requested_resource_id',
            'transfer_observation',
            'unit_destination',
            'unit_origin',
            'created_at',
            'updated_at',
        ];

        $attendanceKeys = [
            'id',
            'urc_id',
            'created_by',
            'attendance_sequence_per_ticket',
            'attendance_status_id',
            'number',
            'created_at',
            'updated_at',
            'ticket',
            'patient',
            'observations',
        ];

        $response->assertOk();
        $response->assertJsonFragment(
            Arr::only($attendance->attendable->toArray(), $secondaryAttendanceKeys)
        );
        $response->assertJsonStructure(array_merge(
            $secondaryAttendanceKeys,
            ['attendance' => $attendanceKeys],
        ));
    }

    public function test_user_can_list_recently_created_secondary_attendances()
    {
        Sanctum::actingAs($this->superAdminUser);

        $tickets = Ticket::factory()
            ->count(21)
            ->recycle([
                $this->superAdminUser,
                $this->superAdminUser->currentUrgencyRegulationCenter,
            ])
            ->create([
                'ticket_type_id' => TicketTypeEnum::SECONDARY_OCCURRENCE->value,
                'number_of_victims' => 1,
            ]);

        $response = $this->getJson('/api/ticket/secondary-attendance');

        $response->assertJsonStructure([
            'results',
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'per_page',
                'to',
                'total',
            ],
        ]);

        $tickets->sortBy('opening_at')->take(20)->map(fn ($ticket) => $response->assertJsonFragment(['id' => $ticket->attendances()->first()->id]));
    }

    #[DataProvider('searchCases')]
    public function test_user_can_search_secondary_attendances_by($field, $tickets)
    {
        $this->actingAs($this->superAdminUser);
        $tickets = $tickets($this->superAdminUser);

        [$searchedTicket, $omittedTicket] = $tickets;

        $response = $this->getJson(route('ticket.secondary-attendance.index', ['search' => data_get($searchedTicket, $field)]));

        $response->assertJsonFragment(['id' => $searchedTicket->id]);
        $response->assertJsonMissing(['id' => $omittedTicket->id]);
    }

    public static function searchCases()
    {
        $newTicket = fn (User $user) => Ticket::factory()
            ->count(2)
            ->recycle([
                $user,
                $user->currentUrgencyRegulationCenter,
            ])
            ->create([
                'ticket_type_id' => TicketTypeEnum::SECONDARY_OCCURRENCE->value,
                'number_of_victims' => 1,
            ]);

        yield 'patient name' => [
            'attendances.0.patient.name',
            fn (User $user) => $newTicket($user)->load('attendances.patient'),
        ];

        yield 'city name' => [
            'city.name',
            fn (User $user) => $newTicket($user)->load('city'),
        ];

        yield 'ticket sequence' => [
            'ticket_sequence_per_urgency_regulation_center',
            fn (User $user) => $newTicket($user),
        ];
    }

    public static function invalidPayloads()
    {
        yield 'ticket_type_id is required' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['ticket_type_id' => null]),
            fn () => ['ticket_type_id' => __('request_messages/store_secondary_attendance.ticket_type_id.required')],
        ];

        yield 'ticket_type_id is numeric' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['ticket_type_id' => 'non numeric']),
            fn () => ['ticket_type_id' => __('request_messages/store_secondary_attendance.ticket_type_id.numeric')],
        ];

        yield 'ticket_type_id is secondary occurrence' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['ticket_type_id' => TicketTypeEnum::PRIMARY_OCCURRENCE]),
            fn () => ['ticket_type_id' => __('request_messages/store_secondary_attendance.ticket_type_id.in')],
        ];

        yield 'opening_at is required' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['opening_at' => null]),
            fn () => ['opening_at' => __('request_messages/store_secondary_attendance.opening_at.required')],
        ];

        yield 'opening_at must be a date' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['opening_at' => 'not a date']),
            fn () => ['opening_at' => __('request_messages/store_secondary_attendance.opening_at.date')],
        ];

        yield 'city_id is required' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['city_id' => null]),
            fn () => ['city_id' => __('request_messages/store_secondary_attendance.city_id.required')],
        ];

        yield 'city_id is numeric' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['city_id' => 'not a number']),
            fn () => ['city_id' => __('request_messages/store_secondary_attendance.city_id.numeric')],
        ];

        yield 'city_id is a valid id' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['city_id' => 99999]),
            fn () => ['city_id' => __('request_messages/store_secondary_attendance.city_id.exists')],
        ];

        yield 'requester.name is required' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requester' => ['name' => null]]),
            fn () => ['requester.name' => __('request_messages/store_secondary_attendance.requester.name.required')],
        ];

        yield 'requester.name is string' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requester' => ['name' => 1234567890]]),
            fn () => ['requester.name' => __('request_messages/store_secondary_attendance.requester.name.string')],
        ];

        yield 'requester.primary_phone is required without secondary_phone' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requester' => ['primary_phone' => null, 'secondary_phone' => null]]),
            fn () => ['requester.secondary_phone' => __('request_messages/store_secondary_attendance.requester.primary_phone.required_without')],
        ];

        yield 'requester.primary_phone is string' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requester' => ['primary_phone' => 1234567890]]),
            fn () => ['requester.primary_phone' => __('request_messages/store_secondary_attendance.requester.primary_phone.string')],
        ];

        yield 'requester.secondary_phone is string' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requester' => ['secondary_phone' => 1234567890]]),
            fn () => ['requester.secondary_phone' => __('request_messages/store_secondary_attendance.requester.secondary_phone.string')],
        ];

        yield 'requester.secondary_phone is required without primary_phone' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requester' => ['secondary_phone' => null, 'primary_phone' => null]]),
            fn () => ['requester.secondary_phone' => __('request_messages/store_secondary_attendance.requester.secondary_phone.required_without')],
        ];

        yield 'requester.requester_type_id is numeric' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requester' => ['requester_type_id' => 'not a number']]),
            fn () => ['requester.requester_type_id' => __('request_messages/store_secondary_attendance.requester.requester_type_id.numeric')],
        ];

        yield 'requester.requester_type_id is valid enum case' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requester' => ['requester_type_id' => RequesterTypeEnum::REQUESTER_SECONDARY_ATTENDANCE]]),
            fn () => ['requester.requester_type_id' => __('request_messages/store_secondary_attendance.requester.requester_type_id.in')],
        ];

        //patients

        yield 'protocol is string' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['protocol' => 1234567890]),
            fn () => ['protocol' => __('request_messages/store_secondary_attendance.protocol.string')],
        ];

        yield 'in_central_bed is boolean' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['in_central_bed' => 'not a boolean']),
            fn () => ['in_central_bed' => __('request_messages/store_secondary_attendance.in_central_bed.boolean')],
        ];

        yield 'transfer_reason_id is numeric' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['transfer_reason_id' => 'not a number']),
            fn () => ['transfer_reason_id' => __('request_messages/store_secondary_attendance.transfer_reason_id.numeric')],
        ];

        yield 'transfer_reason_id is valid enum case' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['transfer_reason_id' => 'not a valid enum']),
            fn () => ['transfer_reason_id' => __('request_messages/store_secondary_attendance.transfer_reason_id.enum')],
        ];

        yield 'hipotesis is required' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['diagnostic_hypothesis' => null]),
            fn () => ['diagnostic_hypothesis' => __('request_messages/store_secondary_attendance.diagnostic_hypothesis.required')],
        ];

        yield 'hipotesis is string' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['diagnostic_hypothesis' => 1234567890]),
            fn () => ['diagnostic_hypothesis' => __('request_messages/store_secondary_attendance.diagnostic_hypothesis.string')],
        ];

        yield 'hipotesis is max 500' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['diagnostic_hypothesis' => str_repeat('a', 501)]),
            fn () => ['diagnostic_hypothesis' => __('request_messages/store_secondary_attendance.diagnostic_hypothesis.max')],
        ];

        yield 'unit_origin_id is required' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['unit_origin_id' => null]),
            fn () => ['unit_origin_id' => __('request_messages/store_secondary_attendance.unit_origin_id.required')],
        ];

        yield 'unit_origin_id is uuid' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['unit_origin_id' => 'not a uuid']),
            fn () => ['unit_origin_id' => __('request_messages/store_secondary_attendance.unit_origin_id.uuid')],
        ];

        yield 'unit_origin_id is valid id' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['unit_origin_id' => Uuid::uuid4()]),
            fn () => ['unit_origin_id' => __('request_messages/store_secondary_attendance.unit_origin_id.exists')],
        ];

        yield 'unit_destination_id is required' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['unit_destination_id' => null]),
            fn () => ['unit_destination_id' => __('request_messages/store_secondary_attendance.unit_destination_id.required')],
        ];

        yield 'unit_destination_id is uuid' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['unit_destination_id' => 'not a uuid']),
            fn () => ['unit_destination_id' => __('request_messages/store_secondary_attendance.unit_destination_id.uuid')],
        ];

        yield 'unit_destination_id is valid id' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['unit_destination_id' => Uuid::uuid4()]),
            fn () => ['unit_destination_id' => __('request_messages/store_secondary_attendance.unit_destination_id.exists')],
        ];

        yield 'complement_origin is string' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['complement_origin' => 1234567890]),
            fn () => ['complement_origin' => __('request_messages/store_secondary_attendance.complement_origin.string')],
        ];

        yield 'complement_destination is string' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['complement_destination' => 1234567890]),
            fn () => ['complement_destination' => __('request_messages/store_secondary_attendance.complement_destination.string')],
        ];

        yield 'requested_resource_id is numeric' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requested_resource_id' => 'not a number']),
            fn () => ['requested_resource_id' => __('request_messages/store_secondary_attendance.requested_resource_id.numeric')],
        ];

        yield 'requested_resource_id is valid enum case' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['requested_resource_id' => 99999]),
            fn () => ['requested_resource_id' => __('request_messages/store_secondary_attendance.requested_resource_id.enum')],
        ];

        yield 'transfer_observation is string' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['transfer_observation' => 1234567890]),
            fn () => ['transfer_observation' => __('request_messages/store_secondary_attendance.transfer_observation.string')],
        ];

        yield 'transfer_observation is max 1000' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['transfer_observation' => str_repeat('a', 1001)]),
            fn () => ['transfer_observation' => __('request_messages/store_secondary_attendance.transfer_observation.max')],
        ];

        yield 'observations is string' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['observations' => 1234567890]),
            fn () => ['observations' => __('request_messages/store_secondary_attendance.observations.string')],
        ];

        yield 'observations is max 3000' => [
            fn () => StoreSecondaryAttendanceRequestFactory::new(['observations' => str_repeat('a', 3001)]),
            fn () => ['observations' => __('request_messages/store_secondary_attendance.observations.max')],
        ];
    }

    public static function allowedRoles(): array
    {
        return [
            RolesEnum::ADMIN,
            RolesEnum::SUPER_ADMIN,
            RolesEnum::TARM,
            RolesEnum::MEDIC,
            RolesEnum::RADIO_OPERATOR,
            RolesEnum::TEAM_LEADER,
        ];
    }
}
