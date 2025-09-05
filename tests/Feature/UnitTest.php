<?php

namespace Tests\Feature;

use App\Enums\RolesEnum;
use App\Models\Unit;
use App\Models\User;
use App\Traits\TestsRoles;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Contracts\HasRouteTests;
use Tests\RequestFactories\UnitRequestFactory;
use Tests\TestCase;

class UnitTest extends TestCase implements HasRouteTests
{
    use DatabaseTransactions, TestsRoles;

    public array $testEndpoints;

    public $requestFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestFactory = new UnitRequestFactory();
    }

    public function endpoints(): array
    {
        $unit = Unit::factory()->create();

        return [
            'GET' => ["/api/units/$unit->id"],
            'POST' => ['api/units'],
            'PUT' => [
                "api/units/$unit->id",
                "api/units/change-status/$unit->id",
            ],
        ];
    }

    public static function availableRoles()
    {
        $roles = RolesEnum::cases();

        foreach ($roles as $role) {
            yield "{$role->value}" => [
                $role->value,
                in_array($role->value, [RolesEnum::SUPER_ADMIN->value, RolesEnum::ADMIN->value]),
            ];
        }
    }

    public function test_user_can_create_unit_with_valid_data()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $payload = UnitRequestFactory::new()->create();

        // Act
        $response = $this->postJson('api/units', $payload);

        // Assert
        $response->assertOk();

        $response->assertJsonStructure(
            array_keys($payload)
        );

        $response->assertJsonFragment(array_merge($payload, ['id' => $response['id']]));

        $this->assertDatabaseHas('units', $payload);
    }

    #[DataProvider('invalidPayloads')]
    public function test_user_cannot_create_unit_with_invalid_data($invalidPayload, $error)
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        Unit::factory()->create(['national_health_registration' => '123456']);

        // Act
        $response = $this->postJson('api/units', UnitRequestFactory::new($invalidPayload())->create());

        // Assert
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($error());
    }

    public function test_user_can_show_all_units()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $units = Unit::factory()->count(11)->create();

        // Act
        $response = $this->getJson('api/units');

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

        $units->take(10)->map(function ($unit) use ($response) {
            $response->assertJsonFragment(['id' => $unit->id]);
        });
    }

    public function test_user_can_show_specific_unit()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $unit = Unit::factory()->create();

        // Act
        $response = $this->getJson("api/units/$unit->id");

        // Assert
        $response->assertOk();
        $response->assertJsonFragment(['id' => $unit->id]);
    }

    public function test_user_can_update_unit_with_valid_data()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $unit = Unit::factory()->create();
        $payload = UnitRequestFactory::new()->create();

        // Act
        $response = $this->putJson("api/units/$unit->id", $payload);

        // Assert
        $response->assertOk();
        $response->assertJsonFragment($payload);

        $this->assertDatabaseHas('units', $payload);
    }

    public function test_user_can_change_unit_status()
    {
        // Arrange
        Sanctum::actingAs(($this->superAdminUser));
        $unit = Unit::factory()->create();

        // Act
        $response = $this->putJson("api/units/change-status/$unit->id");

        // Assert
        $response->assertOk();
        $response->assertJsonFragment(['is_active' => !$unit->is_active]);
    }

    #[DataProvider('searchCases')]
    public function test_user_can_search_units($field, $search)
    {
        //Arrange
        Sanctum::actingAs($this->superAdminUser);
        $search = $search($this->superAdminUser);

        [$searched, $omittedSearch] = $search;

        // Act
        $response = $this->getJson(route('units.index', ['search' => data_get($searched, $field)]));

        // Assert
        $response->assertOk();

        $response->assertJsonFragment(['id' => $searched->id]);
        $response->assertJsonMissing(['id' => $omittedSearch->id]);
    }

    public static function searchCases()
    {
        $newUnit = fn (User $user) => Unit::factory()
            ->count(2)
            ->recycle([
                $user,
                $user->currentUrgencyRegulationCenter,
            ])
            ->create();

        yield 'unit name' => [
            'name',
            fn (User $user) => $newUnit($user),
        ];

        yield 'unit type name' => [
            'unitType.name',
            fn (User $user) => $newUnit($user)->load('unitType'),
        ];

        yield 'city name' => [
            'city.name',
            fn (User $user) => $newUnit($user)->load('city'),
        ];
    }

    public static function invalidPayloads()
    {
        yield 'unit type id must be a valid case' => [
            fn () => UnitRequestFactory::new(['unit_type_id' => 0]),
            fn () => ['unit_type_id' => __('request_messages/store_unit.unit_type_id.enum')],
        ];

        yield 'unit type id is required' => [
            fn () => UnitRequestFactory::new(['unit_type_id' => null]),
            fn () => ['unit_type_id' => __('request_messages/store_unit.unit_type_id.required')],
        ];

        yield 'unit type id must be an integer' => [
            fn () => UnitRequestFactory::new(['unit_type_id' => 'not a number']),
            fn () => ['unit_type_id' => __('request_messages/store_unit.unit_type_id.integer')],
        ];

        yield 'city id is required' => [
            fn () => UnitRequestFactory::new(['city_id' => null]),
            fn () => ['city_id' => __('request_messages/store_unit.city_id.required')],
        ];

        yield 'city id must be an integer' => [
            fn () => UnitRequestFactory::new(['city_id' => 'string']),
            fn () => ['city_id' => __('request_messages/store_unit.city_id.integer')],
        ];

        yield 'city id must exist' => [
            fn () => UnitRequestFactory::new(['city_id' => 0]),
            fn () => ['city_id' => __('request_messages/store_unit.city_id.exists')],
        ];

        yield 'name is required' => [
            fn () => UnitRequestFactory::new(['name' => null]),
            fn () => ['name' => __('request_messages/store_unit.name.required')],
        ];

        yield 'name must be a string' => [
            fn () => UnitRequestFactory::new(['name' => 0]),
            fn () => ['name' => __('request_messages/store_unit.name.string')],
        ];

        yield 'name must not have more than 255 characters' => [
            fn () => UnitRequestFactory::new(['name' => str_repeat('a', 256)]),
            fn () => ['name' => __('request_messages/store_unit.name.max')],
        ];

        yield 'national health registration is required' => [
            fn () => UnitRequestFactory::new(['national_health_registration' => null]),
            fn () => ['national_health_registration' => __('request_messages/store_unit.national_health_registration.required')],
        ];

        yield 'national health registration must be a string' => [
            fn () => UnitRequestFactory::new(['national_health_registration' => 0]),
            fn () => ['national_health_registration' => __('request_messages/store_unit.national_health_registration.string')],
        ];

        yield 'national health registration must not have more than 40 characters' => [
            fn () => UnitRequestFactory::new(['national_health_registration' => str_repeat('a', 41)]),
            fn () => ['national_health_registration' => __('request_messages/store_unit.national_health_registration.max')],
        ];

        yield 'national health registration must be unique' => [
            fn () => UnitRequestFactory::new(['national_health_registration' => '123456']),
            fn () => ['national_health_registration' => __('request_messages/store_unit.national_health_registration.unique')],
        ];

        yield 'street must be a string' => [
            fn () => UnitRequestFactory::new(['street' => 0]),
            fn () => ['street' => __('request_messages/store_unit.street.string')],
        ];

        yield 'street must not have more than 100 characters' => [
            fn () => UnitRequestFactory::new(['street' => str_repeat('a', 101)]),
            fn () => ['street' => __('request_messages/store_unit.street.max')],
        ];

        yield 'house number must be a string' => [
            fn () => UnitRequestFactory::new(['house_number' => 0]),
            fn () => ['house_number' => __('request_messages/store_unit.house_number.string')],
        ];

        yield 'house number must not have more than 20 characters' => [
            fn () => UnitRequestFactory::new(['house_number' => str_repeat('a', 21)]),
            fn () => ['house_number' => __('request_messages/store_unit.house_number.max')],
        ];

        yield 'zip code must be a string' => [
            fn () => UnitRequestFactory::new(['zip_code' => 0]),
            fn () => ['zip_code' => __('request_messages/store_unit.zip_code.string')],
        ];

        yield 'zip code must not have more than 255 characters' => [
            fn () => UnitRequestFactory::new(['zip_code' => str_repeat('a', 256)]),
            fn () => ['zip_code' => __('request_messages/store_unit.zip_code.max')],
        ];

        yield 'neighborhood must be a string' => [
            fn () => UnitRequestFactory::new(['neighborhood' => 0]),
            fn () => ['neighborhood' => __('request_messages/store_unit.neighborhood.string')],
        ];

        yield 'neighborhood must not have more than 100 characters' => [
            fn () => UnitRequestFactory::new(['neighborhood' => str_repeat('a', 101)]),
            fn () => ['neighborhood' => __('request_messages/store_unit.neighborhood.max')],
        ];

        yield 'complement must be a string' => [
            fn () => UnitRequestFactory::new(['complement' => 0]),
            fn () => ['complement' => __('request_messages/store_unit.complement.string')],
        ];

        yield 'latitude must be a string' => [
            fn () => UnitRequestFactory::new(['latitude' => 0]),
            fn () => ['latitude' => __('request_messages/store_unit.latitude.string')],
        ];

        yield 'longitude must be a string' => [
            fn () => UnitRequestFactory::new(['longitude' => 0]),
            fn () => ['longitude' => __('request_messages/store_unit.longitude.string')],
        ];

        yield 'telephone must be a string' => [
            fn () => UnitRequestFactory::new(['telephone' => 0]),
            fn () => ['telephone' => __('request_messages/store_unit.telephone.string')],
        ];

        yield 'telephone must not have more than 40 characters' => [
            fn () => UnitRequestFactory::new(['telephone' => str_repeat('a', 41)]),
            fn () => ['telephone' => __('request_messages/store_unit.telephone.max')],
        ];

        yield 'company registration number must be a string' => [
            fn () => UnitRequestFactory::new(['company_registration_number' => 0]),
            fn () => ['company_registration_number' => __('request_messages/store_unit.company_registration_number.string')],
        ];

        yield 'company registration number must not have more than 40 characters' => [
            fn () => UnitRequestFactory::new(['company_registration_number' => str_repeat('a', 41)]),
            fn () => ['company_registration_number' => __('request_messages/store_unit.company_registration_number.max')],
        ];

        yield 'company name must be a string' => [
            fn () => UnitRequestFactory::new(['company_name' => 0]),
            fn () => ['company_name' => __('request_messages/store_unit.company_name.string')],
        ];
    }
}
