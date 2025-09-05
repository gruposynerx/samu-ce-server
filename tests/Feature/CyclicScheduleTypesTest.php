<?php

namespace Tests\Feature;

use App\Enums\RolesEnum;
use App\Models\CyclicScheduleType;
use App\Models\User;
use App\Traits\TestsRoles;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Contracts\HasRouteTests;
use Tests\RequestFactories\CyclicScheduleTypesRequestFactory;
use Tests\TestCase;

class CyclicScheduleTypesTest extends TestCase implements HasRouteTests
{
    use DatabaseTransactions, TestsRoles;

    public array $testEndpoints;

    public $requestFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestFactory = new CyclicScheduleTypesRequestFactory();
    }

    public function endpoints(): array
    {
        $cyclicScheduleType = CyclicScheduleType::factory()->create();

        return [
            'GET' => ['/api/cyclic-schedule-type'],
            'POST' => ['/api/cyclic-schedule-type'],
            'PUT' => [
                "/api/cyclic-schedule-type/$cyclicScheduleType->id",
                "/api/cyclic-schedule-type/change-status/$cyclicScheduleType->id",
            ],
        ];
    }

    public static function availableRoles()
    {
        $roles = RolesEnum::cases();

        foreach ($roles as $role) {
            yield "{$role->value}" => [
                $role->value,
                in_array($role->value, [
                    RolesEnum::SUPER_ADMIN->value,
                    RolesEnum::ADMIN->value,
                    RolesEnum::MEDIC->value,
                    RolesEnum::ATTENDANCE_OR_AMBULANCE_TEAM->value,
                    RolesEnum::MANAGER->value,
                ]),
            ];
        }
    }

    public function test_user_can_create_cyclic_schedule_types_with_valid_payload()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $payload = CyclicScheduleTypesRequestFactory::new()->create();

        // Act
        $response = $this->postJson('/api/cyclic-schedule-type', $payload);

        // Assert
        $response->assertCreated();
        $response->assertJsonStructure(
            array_merge(
                array_keys($payload),
                ['id', 'created_at', 'updated_at', 'is_active']
            )
        );

        $this->assertDatabaseHas('cyclic_schedule_types', $payload);
    }

    #[DataProvider('invalidPayloads')]
    public function test_create_cyclic_schedules_rule($invalidPayloads, $error)
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);

        // Act
        $response = $this->postJson('api/cyclic-schedule-type', CyclicScheduleTypesRequestFactory::new($invalidPayloads())->create());

        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($error());
    }

    public function test_user_can_show_cyclic_schedule_types()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $cyclicScheduleTypes = CyclicScheduleType::factory()->count(11)->create();

        // Act
        $response = $this->getJson('/api/cyclic-schedule-type');

        // Assert
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

        $cyclicScheduleTypes->sortBy('created_at')->take(10)->map(fn ($x) => $response->assertJsonFragment(['id' => $x->id]));

        $this->assertEquals(1, $response['meta']['current_page']);
        $this->assertEquals(10, count($response['results']));
    }

    public function test_user_can_update_cyclic_schedule_types_with_valid_payload()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $cyclicScheduleType = CyclicScheduleType::factory()->create();
        $payload = CyclicScheduleTypesRequestFactory::new()->create();

        // Act
        $response = $this->putJson("/api/cyclic-schedule-type/$cyclicScheduleType->id", $payload);

        // Assert
        $response->assertOk();
        $response->assertJsonStructure(
            array_merge(
                array_keys($payload),
                ['id', 'created_at', 'updated_at', 'is_active']
            )
        );

        $this->assertDatabaseHas('cyclic_schedule_types', $payload);
    }

    public function test_user_can_change_status_cyclic_schedule_types()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $cyclicScheduleType = CyclicScheduleType::factory()->create();

        // Act
        $response = $this->putJson("/api/cyclic-schedule-type/change-status/$cyclicScheduleType->id");

        // Assert
        $response->assertOk();
        $response->assertJsonFragment([
            'is_active' => !$cyclicScheduleType->is_active,
        ]);
    }

    #[DataProvider('searchCases')]
    public function test_user_can_search_cyclic_schedule_types_by_different_fields($field, $cyclicSchedules)
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $cyclicSchedules = $cyclicSchedules($this->superAdminUser);

        [$searchedCyclicSchedules, $omittedCyclicSchedules] = $cyclicSchedules;

        // Act
        $response = $this->getJson(route('cyclic-schedule-type.index', ['search' => data_get($searchedCyclicSchedules, $field)]));

        // Assert
        $response->assertJsonFragment(['id' => $searchedCyclicSchedules->id]);
        $response->assertJsonMissing(['id' => $omittedCyclicSchedules->id]);
        $response->assertJsonFragment(['total' => 1]);
    }

    public static function searchCases()
    {
        $newCyclicSchedule = fn (User $user) => CyclicScheduleType::factory()
            ->count(2)
            ->recycle([
                $user,
                $user->currentUrgencyRegulationCenter,
            ])
            ->create();

        yield 'name' => [
            'name',
            fn (User $user) => $newCyclicSchedule($user),
        ];

        yield 'work_hours' => [
            'work_hours',
            fn (User $user) => $newCyclicSchedule($user),
        ];

        yield 'break_hours' => [
            'break_hours',
            fn (User $user) => $newCyclicSchedule($user),
        ];
    }

    public static function invalidPayloads()
    {
        yield 'name is required' => [
            fn () => CyclicScheduleTypesRequestFactory::new(['name' => null]),
            fn () => ['name' => __('request_messages/store_cyclic_schedule_type.name.required')],
        ];

        yield 'name is not a string' => [
            fn () => CyclicScheduleTypesRequestFactory::new(['name' => 123]),
            fn () => ['name' => __('request_messages/store_cyclic_schedule_type.name.string')],
        ];

        yield 'name is too long' => [
            fn () => CyclicScheduleTypesRequestFactory::new(['name' => str_repeat('a', 101)]),
            fn () => ['name' => __('request_messages/store_cyclic_schedule_type.name.max')],
        ];

        yield 'work_hours is required' => [
            fn () => CyclicScheduleTypesRequestFactory::new(['work_hours' => null]),
            fn () => ['work_hours' => __('request_messages/store_cyclic_schedule_type.work_hours.required')],
        ];

        yield 'work_hours is not an integer' => [
            fn () => CyclicScheduleTypesRequestFactory::new(['work_hours' => 'abc']),
            fn () => ['work_hours' => __('request_messages/store_cyclic_schedule_type.work_hours.integer')],
        ];

        yield 'break_hours is required' => [
            fn () => CyclicScheduleTypesRequestFactory::new(['break_hours' => null]),
            fn () => ['break_hours' => __('request_messages/store_cyclic_schedule_type.break_hours.required')],
        ];

        yield 'break_hours is not an integer' => [
            fn () => CyclicScheduleTypesRequestFactory::new(['break_hours' => 'abc']),
            fn () => ['break_hours' => __('request_messages/store_cyclic_schedule_type.break_hours.integer')],
        ];
    }
}
