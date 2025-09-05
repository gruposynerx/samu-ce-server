<?php

namespace Tests\Feature\Attendance;

use App\Enums\AttendanceStatusEnum;
use App\Enums\RolesEnum;
use App\Enums\TicketTypeEnum;
use App\Events\RefreshAttendance\RefreshPrimaryAttendance;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\RequestFactories\StorePrimaryAttendanceRequestFactory;
use Tests\TestCase;

class PrimaryAttendanceTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $apiBaseUrl = config('external_services.zapi_url') . '/send-text';

        Http::fake([
            $apiBaseUrl => Http::response([
                'zaapId' => '3999984263738042930CD6ECDE9VDWSA',
                'messageId' => 'D241XXXX732339502B68',
                'id' => 'D241XXXX732339502B68',
            ]),
        ]);
    }

    public function test_user_can_create_primary_attendance_with_valid_data()
    {
        Sanctum::actingAs($this->superAdminUser);

        // remover o ticket type id dos atributos depois que SecondaryAttendanceFactory for implementado
        $lastTicket = Ticket::factory()
            ->recycle($this->superAdminUser->currentUrgencyRegulationCenter)
            ->create([
                'ticket_type_id' => TicketTypeEnum::PRIMARY_OCCURRENCE->value,
            ]);

        Event::fake(RefreshPrimaryAttendance::class);

        $payload = StorePrimaryAttendanceRequestFactory::new()->create();

        $response = $this->postJson('/api/ticket/primary-attendance', $payload);

        $response->assertOk();

        $parsedOpeningAt = Carbon::parse($payload['opening_at'], 'America/Fortaleza')->timezone('UTC')->format('Y-m-d H:i:s.u');
        $primaryAttendanceData = Arr::except($payload, ['ticket_type_id', 'patients', 'requester', 'multiple_victims', 'number_of_victims', 'opening_at', 'city_id']);
        $ticketData = array_merge(
            Arr::only($payload, ['ticket_type_id', 'city_id', 'multiple_vzictims', 'number_of_victims', 'opening_at']),
            [
                'ticket_sequence_per_urgency_regulation_center' => $lastTicket->ticket_sequence_per_urgency_regulation_center + 1,
                'opening_at' => $parsedOpeningAt,
            ]
        );
        $ticketData['opening_at'] = Carbon::parse($ticketData['opening_at'])->format('Y-m-d H:i:s');
        $requesterData = $payload['requester'];

        $this->assertDatabaseHas('primary_attendances', $primaryAttendanceData);
        $this->assertDatabaseHas('tickets', $ticketData);
        $this->assertDatabaseHas('requesters', $requesterData);
        $this->assertDatabaseCount('attendance_logs', $payload['number_of_victims'] + $lastTicket->attendances()->count());

        foreach ($payload['patients'] as $index => $patient) {
            $this->assertDatabaseHas('patients', $patient);

            $attendanceData = [
                'created_by' => $this->superAdminUser->id,
                'attendance_status_id' => AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION->value,
                'attendance_sequence_per_ticket' => $index + 1,
            ];

            $this->assertDatabaseHas('attendances', $attendanceData);
        }

        Event::assertDispatched(RefreshPrimaryAttendance::class, 1);
    }

    #[DataProvider('invalidPayloads')]
    public function test_create_primary_attendance_rule($invalidPayload, $error)
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->postJson('/api/ticket/primary-attendance', StorePrimaryAttendanceRequestFactory::new($invalidPayload())->create());

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($error());
    }

    public function test_user_with_correct_roles_can_access_route()
    {
        $roles = Role::whereIn('name', $this->allowedRoles())->get();

        $user = $this->superAdminUser;

        foreach ($roles as $role) {
            $user->update(['current_role' => $role->id]);
            $user->syncRoles([$role->name]);
            $user->refresh();
            Sanctum::actingAs($user);

            $response = $this->postJson('/api/ticket/primary-attendance', StorePrimaryAttendanceRequestFactory::new()->create());
            $response->assertOk();
        }
    }

    public function test_user_with_incorrect_roles_cannot_access_route()
    {
        $unallowedRoles = Role::whereNotIn('name', $this->allowedRoles())->get();

        $user = $this->superAdminUser;

        foreach ($unallowedRoles as $role) {
            $user->update(['current_role' => $role->id]);
            $user->syncRoles([$role->name]);
            $user->refresh();
            Sanctum::actingAs($user);

            $response = $this->postJson('/api/ticket/primary-attendance', StorePrimaryAttendanceRequestFactory::new()->create());
            $response->assertForbidden();
        }
    }

    public function test_user_can_show_data_from_recently_created_primary_attendance()
    {
        Sanctum::actingAs($this->superAdminUser);

        $ticket = Ticket::factory()
            ->recycle($this->superAdminUser->currentUrgencyRegulationCenter)
            ->create([
                'ticket_type_id' => TicketTypeEnum::PRIMARY_OCCURRENCE->value,
                'number_of_victims' => 1,
                'multiple_victims' => false,
            ]);
        $attendance = $ticket->attendances()->first();

        $response = $this->getJson("/api/ticket/primary-attendance/{$attendance->id}");

        $primaryAttendanceKeys = [
            'id',
            'street',
            'house_number',
            'neighborhood',
            'reference_place',
            'primary_complaint',
            'observations',
            'distance_type_id',
            'location_type_id',
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
            Arr::only($attendance->attendable->toArray(), $primaryAttendanceKeys)
        );
        $response->assertJsonStructure(array_merge(
            $primaryAttendanceKeys,
            ['attendance' => $attendanceKeys],
        ));
    }

    public function test_user_can_list_recently_created_primary_attendances()
    {
        Sanctum::actingAs($this->superAdminUser);

        $tickets = Ticket::factory()
            ->count(21)
            ->recycle([
                $this->superAdminUser,
                $this->superAdminUser->currentUrgencyRegulationCenter,
            ])
            ->create([
                'ticket_type_id' => TicketTypeEnum::PRIMARY_OCCURRENCE->value,
                'number_of_victims' => 1,
                'multiple_victims' => false,
            ]);

        $response = $this->getJson('/api/ticket/primary-attendance');

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
    public function test_user_can_search_primary_attendances_by($field, $tickets)
    {
        $this->actingAs($this->superAdminUser);
        $tickets = $tickets($this->superAdminUser);

        [$searchedTicket, $omittedTicket] = $tickets;

        $response = $this->getJson(route('ticket.primary-attendance.index', ['search' => data_get($searchedTicket, $field)]));

        $response->assertJsonFragment(['id' => $searchedTicket->id]);
        $response->assertJsonMissing(['id' => $omittedTicket->id]);
        $response->assertJsonFragment([
            'total' => 1,
        ]);
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
                'ticket_type_id' => TicketTypeEnum::PRIMARY_OCCURRENCE->value,
                'number_of_victims' => 1,
                'multiple_victims' => false,
            ]);

        yield 'patient name' => [
            'attendances.0.patient.name',
            fn (User $user) => $newTicket($user)->load('attendances.patient'),
        ];

        yield 'city name' => [
            'city.name',
            fn (User $user) => $newTicket($user)->load('city'),
        ];

        yield 'neighborhood' => [
            'attendances.0.attendable.neighborhood',
            fn (User $user) => $newTicket($user)->load('attendances.attendable'),
        ];

        yield 'ticket sequence' => [
            'ticket_sequence_per_urgency_regulation_center',
            fn (User $user) => $newTicket($user),
        ];
    }

    public static function invalidPayloads()
    {
        yield 'ticket_type_id is required' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['ticket_type_id' => null]),
            fn () => ['ticket_type_id' => __('request_messages/store_primary_attendance.ticket_type_id.required')],
        ];

        yield 'ticket_type_id is numeric' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['ticket_type_id' => 'non numeric']),
            fn () => ['ticket_type_id' => __('request_messages/store_primary_attendance.ticket_type_id.numeric')],
        ];

        yield 'ticket_type_id is primary occurrence' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['ticket_type_id' => TicketTypeEnum::SECONDARY_OCCURRENCE]),
            fn () => ['ticket_type_id' => __('request_messages/store_primary_attendance.ticket_type_id.in')],
        ];

        yield 'opening_at is required' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['opening_at' => null]),
            fn () => ['opening_at' => __('request_messages/store_primary_attendance.opening_at.required')],
        ];

        yield 'opening_at must be a date' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['opening_at' => 'not a date']),
            fn () => ['opening_at' => __('request_messages/store_primary_attendance.opening_at.date')],
        ];

        yield 'city_id is required' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['city_id' => null]),
            fn () => ['city_id' => __('request_messages/store_primary_attendance.city_id.required')],
        ];

        yield 'city_id is numeric' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['city_id' => 'not a number']),
            fn () => ['city_id' => __('request_messages/store_primary_attendance.city_id.numeric')],
        ];

        yield 'city_id is a valid id' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['city_id' => 99999]),
            fn () => ['city_id' => __('request_messages/store_primary_attendance.city_id.exists')],
        ];

        yield 'multiple_victims is boolean' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['multiple_victims' => 'not a boolean']),
            fn () => ['multiple_victims' => __('request_messages/store_primary_attendance.multiple_victims.boolean')],
        ];

        yield 'number_of_victims is numeric' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['number_of_victims' => 'not a number']),
            fn () => ['number_of_victims' => __('request_messages/store_primary_attendance.number_of_victims.numeric')],
        ];

        yield 'requester.name is required' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['requester' => ['name' => null]]),
            fn () => ['requester.name' => __('request_messages/store_primary_attendance.requester.name.required')],
        ];

        yield 'requester.name is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['requester' => ['name' => 1234567890]]),
            fn () => ['requester.name' => __('request_messages/store_primary_attendance.requester.name.string')],
        ];

        yield 'requester.primary_phone is required' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['requester' => ['primary_phone' => null]]),
            fn () => ['requester.primary_phone' => __('request_messages/store_primary_attendance.requester.primary_phone.required')],
        ];

        yield 'requester.primary_phone is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['requester' => ['primary_phone' => 1234567890]]),
            fn () => ['requester.primary_phone' => __('request_messages/store_primary_attendance.requester.primary_phone.string')],
        ];

        yield 'requester.secondary_phone is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['requester' => ['secondary_phone' => 1234567890]]),
            fn () => ['requester.secondary_phone' => __('request_messages/store_primary_attendance.requester.secondary_phone.string')],
        ];

        yield 'requester.requester_type_id is numeric' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['requester' => ['requester_type_id' => 'not a number']]),
            fn () => ['requester.requester_type_id' => __('request_messages/store_primary_attendance.requester.requester_type_id.numeric')],
        ];

        yield 'requester.requester_type_id is valid enum case' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['requester' => ['requester_type_id' => 99999]]),
            fn () => ['requester.requester_type_id' => __('request_messages/store_primary_attendance.requester.requester_type_id.enum')],
        ];

        //patients

        yield 'street is required' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['street' => null]),
            fn () => ['street' => __('request_messages/store_primary_attendance.street.required')],
        ];

        yield 'street is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['street' => 1234567890]),
            fn () => ['street' => __('request_messages/store_primary_attendance.street.string')],
        ];

        yield 'street is max 200 characters' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['street' => str_repeat('a', 201)]),
            fn () => ['street' => __('request_messages/store_primary_attendance.street.max')],
        ];

        yield 'house_number is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['house_number' => 1234567890]),
            fn () => ['house_number' => __('request_messages/store_primary_attendance.house_number.string')],
        ];

        yield 'house_number is max 20 characters' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['house_number' => str_repeat('a', 21)]),
            fn () => ['house_number' => __('request_messages/store_primary_attendance.house_number.max')],
        ];

        yield 'neighborhood is required' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['neighborhood' => null]),
            fn () => ['neighborhood' => __('request_messages/store_primary_attendance.neighborhood.required')],
        ];

        yield 'neighborhood is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['neighborhood' => 1234567890]),
            fn () => ['neighborhood' => __('request_messages/store_primary_attendance.neighborhood.string')],
        ];

        yield 'neighborhood is max 100 characters' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['neighborhood' => str_repeat('a', 101)]),
            fn () => ['neighborhood' => __('request_messages/store_primary_attendance.neighborhood.max')],
        ];

        yield 'reference_place is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['reference_place' => 1234567890]),
            fn () => ['reference_place' => __('request_messages/store_primary_attendance.reference_place.string')],
        ];

        yield 'reference_place is max 2000 characters' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['reference_place' => str_repeat('a', 2001)]),
            fn () => ['reference_place' => __('request_messages/store_primary_attendance.reference_place.max')],
        ];

        yield 'primary_complaint is required' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['primary_complaint' => null]),
            fn () => ['primary_complaint' => __('request_messages/store_primary_attendance.primary_complaint.required')],
        ];

        yield 'primary_complaint is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['primary_complaint' => 1234567890]),
            fn () => ['primary_complaint' => __('request_messages/store_primary_attendance.primary_complaint.string')],
        ];

        yield 'observations is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['observations' => 1234567890]),
            fn () => ['observations' => __('request_messages/store_primary_attendance.observations.string')],
        ];

        yield 'observations is max 3000 characters' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['observations' => str_repeat('a', 3001)]),
            fn () => ['observations' => __('request_messages/store_primary_attendance.observations.max')],
        ];

        yield 'distance_type_id is numeric' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['distance_type_id' => 'not a number']),
            fn () => ['distance_type_id' => __('request_messages/store_primary_attendance.distance_type_id.numeric')],
        ];

        yield 'distance_type_id is valid enum case' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['distance_type_id' => 99999]),
            fn () => ['distance_type_id' => __('request_messages/store_primary_attendance.distance_type_id.enum')],
        ];

        yield 'location_type_id is numeric' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['location_type_id' => 'not a number']),
            fn () => ['location_type_id' => __('request_messages/store_primary_attendance.location_type_id.numeric')],
        ];

        yield 'location_type_id is valid enum case' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['location_type_id' => 99999]),
            fn () => ['location_type_id' => __('request_messages/store_primary_attendance.location_type_id.enum')],
        ];

        yield 'location_type_description is string' => [
            fn () => StorePrimaryAttendanceRequestFactory::new(['location_type_description' => 1234567890]),
            fn () => ['location_type_description' => __('request_messages/store_primary_attendance.location_type_description.string')],
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
