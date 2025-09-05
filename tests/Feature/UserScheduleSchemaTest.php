<?php

namespace Tests\Feature;

use App\Enums\PeriodTypeEnum;
use App\Enums\RolesEnum;
use App\Enums\ScheduleTypeEnum;
use App\Models\User;
use App\Models\UserSchedule;
use App\Models\UserScheduleSchema;
use App\Traits\TestsRoles;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Contracts\HasRouteTests;
use Tests\RequestFactories\UserScheduleSchemaRequestFactory;
use Tests\TestCase;

class UserScheduleSchemaTest extends TestCase implements HasRouteTests
{
    use DatabaseTransactions, TestsRoles;

    public array $testEndpoints;

    public $requestFactory;

    public function setUp(): void
    {
        parent::setUp();

        $now = Carbon::now();

        $this->requestFactory = new UserScheduleSchemaRequestFactory([
            'valid_from' => $now->format('Y-m-d'),
            'valid_through' => $now->addWeeks(2)->format('Y-m-d'),
            'days_of_week' => range(0, 6),
        ]);
    }

    public function endpoints(): array
    {
        $now = Carbon::now()->addWeeks(3);

        $userScheduleSchema = UserScheduleSchema::factory()->create([
            'valid_from' => $now->format('Y-m-d'),
            'valid_through' => $now->addWeeks(3)->format('Y-m-d'),
            'days_of_week' => range(0, 6),
        ]);

        return [
            'GET' => [
                "/api/schedules-schema/$userScheduleSchema->id",
            ],
            'POST' => ['/api/schedules-schema'],
            'PUT' => ["/api/schedules-schema/$userScheduleSchema->id"],
        ];
    }

    public static function availableRoles()
    {
        $roles = RolesEnum::cases();

        foreach ($roles as $role) {
            yield "{$role->value}" => [
                $role->value,
                in_array($role->value, [RolesEnum::SUPER_ADMIN->value, RolesEnum::ADMIN->value, RolesEnum::COORDINATOR->value]),
            ];
        }
    }

    #[DataProvider('invalidPayloads')]
    public function test_create_user_schedule_schema_rule($invalidPayload, $error)
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);

        // Act
        $response = $this->postJson('/api/schedules-schema', UserScheduleSchemaRequestFactory::new($invalidPayload())->create());

        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($error());
    }

    public function test_user_can_list_recently_created_schedule_schemas(): void
    {
        //Arrange
        Sanctum::actingAs($this->superAdminUser);
        $carbonDayOfWeek = Carbon::now()->dayOfWeek;

        $userScheduleSchemas = UserScheduleSchema::factory()->count(10)->create([
            'clock_in' => '08:00',
            'clock_out' => '20:00',
            'valid_from' => Carbon::now()->format('Y-m-d'),
            'valid_through' => Carbon::now()->addWeeks(4)->format('Y-m-d'),
            'days_of_week' => ["$carbonDayOfWeek"],
            'schedule_type_id' => ScheduleTypeEnum::DEFAULT->value,
        ]);

        //Act
        $response = $this->getJson(route('schedules-schema.index', [
            'date' => Carbon::now()->format('Y-m-d'),
            'period_type' => PeriodTypeEnum::DAYTIME->value,
        ]));

        //Assert
        $response->assertOk();

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

        $userScheduleSchemas->sortBy('created_at')->take(10)->each(fn ($x) => $response->assertJsonFragment(['id' => $x->schedulable_id]));

        $this->assertEquals(1, $response['meta']['current_page']);
        $this->assertEquals(10, count($response['results']));
    }

    public function test_user_can_show_a_schedule_schema(): void
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $userScheduleSchema = UserScheduleSchema::factory()->create();

        // Act
        $response = $this->getJson("/api/schedules-schema/$userScheduleSchema->id");

        // Assert
        $response->assertOk();

        unset($userScheduleSchema['schedulable_id'], $userScheduleSchema['schedulable_type']);
        $response->assertJsonStructure(
            array_merge(
                array_keys($userScheduleSchema->toArray()),
                ['id', 'created_at', 'updated_at']
            )
        );

        $response->assertJsonFragment($userScheduleSchema->toArray());
    }

    public function test_user_can_create_schedule_schema_with_valid_data(): void
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $payload = UserScheduleSchemaRequestFactory::new()->create();

        // Act
        $response = $this->postJson('/api/schedules-schema', $payload);

        // Assert
        $response->assertOk();

        unset($payload['schedulable_id'], $payload['schedulable_type']);
        $response->assertJsonStructure(
            array_merge(
                array_keys($payload),
                ['id', 'created_at', 'updated_at']
            )
        );

        $userSchedules = $response['schedules'];

        foreach ($userSchedules as $userSchedule) {
            $userSchedule['created_at'] = Carbon::parse($userSchedule['created_at'])->setTimezone('America/Fortaleza');
            $userSchedule['updated_at'] = Carbon::parse($userSchedule['updated_at'])->setTimezone('America/Fortaleza');

            $this->assertDatabaseHas('user_schedules', $userSchedule);
        }

        $daysOfWeekEncoded = json_encode($payload['days_of_week']);

        $this->assertDatabaseHas('user_schedule_schemas', [
            ...$payload,
            'days_of_week' => DB::raw("CAST('{$daysOfWeekEncoded}' AS jsonb)"),
        ]);
    }

    public function test_user_can_update_schedule_schema_with_valid_data(): void
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);

        $payload = UserScheduleSchemaRequestFactory::new()->create();

        $schema = UserScheduleSchema::factory()->create();

        $workDays = CarbonPeriod::between($schema['valid_from'], $schema['valid_through'])
            ->filter(fn ($date) => in_array($date->dayOfWeek, $schema['days_of_week']));

        UserSchedule::factory()->count(count($workDays))->create([
            'schema_id' => $schema->id,
        ]);

        $oldUserSchedules = $schema['schedules'];

        // Act
        $response = $this->putJson("/api/schedules-schema/$schema->id", $payload);

        // Assert
        $response->assertOk();

        unset($payload['schedulable_id'], $payload['schedulable_type']);
        $response->assertJsonStructure(
            array_merge(
                array_keys($payload),
                ['id', 'created_at', 'updated_at']
            )
        );

        $newUserSchedules = $response['schedules'];

        foreach ($oldUserSchedules as $userSchedule) {
            $this->assertDatabaseMissing('user_schedules', $userSchedule->toArray());
        }

        foreach ($newUserSchedules as $userSchedule) {
            $userSchedule['created_at'] = Carbon::parse($userSchedule['created_at'])->subHours(3)->format('Y-m-d H:i:s');
            $userSchedule['updated_at'] = Carbon::parse($userSchedule['updated_at'])->subHours(3)->format('Y-m-d H:i:s');

            $this->assertDatabaseHas('user_schedules', $userSchedule);
        }

        $daysOfWeekEncoded = json_encode($payload['days_of_week']);

        $this->assertDatabaseHas('user_schedule_schemas', [
            ...$payload,
            'days_of_week' => DB::raw("CAST('{$daysOfWeekEncoded}' AS jsonb)"),
        ]);
    }

    public function test_user_cannot_create_schedule_schema_with_invalid_data(): void
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);

        $user = User::factory()->create();
        $allDaysOfWeek = range(0, 6);

        $schema = UserScheduleSchema::factory()->create([
            'user_id' => $user->id,
            'valid_from' => Carbon::now()->format('Y-m-d'),
            'valid_through' => Carbon::now()->addWeeks(4)->format('Y-m-d'),
            'days_of_week' => $allDaysOfWeek,
            'clock_in' => '08:00',
            'clock_out' => '17:00',
        ]);

        $workDays = CarbonPeriod::between($schema['valid_from'], $schema['valid_through'])
            ->filter(fn ($date) => in_array($date->dayOfWeek, $schema['days_of_week']));

        foreach ($workDays as $workDay) {
            $date = $workDay->toDateString();

            UserSchedule::factory()->create([
                'user_id' => $schema->user->id,
                'schema_id' => $schema->id,
                'starts_at' => Carbon::parse("$date $schema->clock_in"),
                'ends_at' => Carbon::parse("$date $schema->clock_out"),
                'occupation_code' => $schema->user->cbo,
            ]);
        }

        $conflictPayload = UserScheduleSchemaRequestFactory::new()->create([
            'user_id' => $user->id,
            'valid_from' => Carbon::now()->addWeeks(2)->format('Y-m-d'),
            'valid_through' => Carbon::now()->addWeeks(3)->format('Y-m-d'),
            'days_of_week' => $allDaysOfWeek,
            'clock_in' => '08:00',
            'clock_out' => '21:00',
        ]);

        // Act
        $response = $this->postJson('/api/schedules-schema', $conflictPayload);

        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['conflict' => 'O profissional já possui escala cadastrada nesse horário.']);
    }

    public static function invalidPayloads()
    {
        yield 'user_id is required' => [
            fn () => UserScheduleSchemaRequestFactory::new(['user_id' => null]),
            fn () => ['user_id' => __('request_messages/store_user_schedule_schema.user_id.required')],
        ];

        yield 'user_id is uuid' => [
            fn () => UserScheduleSchemaRequestFactory::new(['user_id' => 1]),
            fn () => ['user_id' => __('request_messages/store_user_schedule_schema.user_id.uuid')],
        ];

        yield 'user_id exists' => [
            fn () => UserScheduleSchemaRequestFactory::new(['user_id' => Str::orderedUuid()]),
            fn () => ['user_id' => __('request_messages/store_user_schedule_schema.user_id.exists')],
        ];

        yield 'schedulable_id is required' => [
            fn () => UserScheduleSchemaRequestFactory::new(['schedulable_id' => null]),
            fn () => ['schedulable_id' => __('request_messages/store_user_schedule_schema.schedulable_id.required')],
        ];

        yield 'schedulable_id is uuid' => [
            fn () => UserScheduleSchemaRequestFactory::new(['schedulable_id' => 'invalid uuid']),
            fn () => ['schedulable_id' => __('request_messages/store_user_schedule_schema.schedulable_id.uuid')],
        ];

        yield 'schedulable_id exists' => [
            fn () => UserScheduleSchemaRequestFactory::new(['schedulable_id' => Str::orderedUuid()]),
            fn () => ['schedulable_id' => __('request_messages/store_user_schedule_schema.schedulable_id.exists')],
        ];

        yield 'valid_from is required' => [
            fn () => UserScheduleSchemaRequestFactory::new(['valid_from' => null]),
            fn () => ['valid_from' => __('request_messages/store_user_schedule_schema.valid_from.required')],
        ];

        yield 'valid_from is date' => [
            fn () => UserScheduleSchemaRequestFactory::new(['valid_from' => 2022]),
            fn () => ['valid_from' => __('request_messages/store_user_schedule_schema.valid_from.date')],
        ];

        yield 'valid_through is required' => [
            fn () => UserScheduleSchemaRequestFactory::new(['valid_through' => null]),
            fn () => ['valid_through' => __('request_messages/store_user_schedule_schema.valid_through.required')],
        ];

        yield 'valid_through is date' => [
            fn () => UserScheduleSchemaRequestFactory::new(['valid_through' => 'invalid_date']),
            fn () => ['valid_through' => __('request_messages/store_user_schedule_schema.valid_through.date')],
        ];

        yield 'valid_through is after or equal valid_from' => [
            fn () => UserScheduleSchemaRequestFactory::new([
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_through' => Carbon::now()->subDay()->format('Y-m-d'),
            ]),
            fn () => ['valid_through' => __('request_messages/store_user_schedule_schema.valid_through.after_or_equal')],
        ];

        yield 'valid_through is before or equal 3 months after valid_from' => [
            fn () => UserScheduleSchemaRequestFactory::new([
                'valid_from' => Carbon::now()->format('Y-m-d'),
                'valid_through' => Carbon::now()->addMonths(4)->format('Y-m-d'),
            ]),
            fn () => ['valid_through' => __('request_messages/store_user_schedule_schema.valid_through.before_or_equal')],
        ];

        yield 'days_of_week is array' => [
            fn () => UserScheduleSchemaRequestFactory::new(['days_of_week' => 'invalid_array']),
            fn () => ['days_of_week' => __('request_messages/store_user_schedule_schema.days_of_week.array')],
        ];

        yield 'days_of_week is required' => [
            fn () => UserScheduleSchemaRequestFactory::new(['days_of_week' => null]),
            fn () => ['days_of_week' => __('request_messages/store_user_schedule_schema.days_of_week.required')],
        ];

        yield 'days_of_week.* is integer' => [
            fn () => UserScheduleSchemaRequestFactory::new(['days_of_week' => ['invalid']]),
            fn () => ['days_of_week.0' => __('request_messages/store_user_schedule_schema.days_of_week.*.integer')],
        ];

        yield 'clock_in is required' => [
            fn () => UserScheduleSchemaRequestFactory::new(['clock_in' => null]),
            fn () => ['clock_in' => __('request_messages/store_user_schedule_schema.clock_in.required')],
        ];

        yield 'clock_in is string' => [
            fn () => UserScheduleSchemaRequestFactory::new(['clock_in' => 1]),
            fn () => ['clock_in' => __('request_messages/store_user_schedule_schema.clock_in.string')],
        ];

        yield 'clock_out is required' => [
            fn () => UserScheduleSchemaRequestFactory::new(['clock_out' => null]),
            fn () => ['clock_out' => __('request_messages/store_user_schedule_schema.clock_out.required')],
        ];

        yield 'clock_out is string' => [
            fn () => UserScheduleSchemaRequestFactory::new(['clock_out' => 1]),
            fn () => ['clock_out' => __('request_messages/store_user_schedule_schema.clock_out.string')],
        ];

        yield 'schedule_type_id is required' => [
            fn () => UserScheduleSchemaRequestFactory::new(['schedule_type_id' => null]),
            fn () => ['schedule_type_id' => __('request_messages/store_user_schedule_schema.schedule_type_id.required')],
        ];

        yield 'schedule_type_id is enum' => [
            fn () => UserScheduleSchemaRequestFactory::new(['schedule_type_id' => 'invalid_enum']),
            fn () => ['schedule_type_id' => __('request_messages/store_user_schedule_schema.schedule_type_id.enum')],
        ];
    }
}
