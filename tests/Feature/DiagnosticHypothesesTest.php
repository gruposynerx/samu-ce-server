<?php

namespace Tests\Feature;

use App\Enums\NatureTypeEnum;
use App\Enums\RolesEnum;
use App\Models\DiagnosticHypothesis;
use App\Traits\TestsRoles;
use Database\Factories\DiagnosticHypothesisRequestFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Contracts\HasRouteTests;
use Tests\TestCase;

class DiagnosticHypothesesTest extends TestCase implements HasRouteTests
{
    use DatabaseTransactions, TestsRoles;

    public array $testEndpoints;

    public $requestFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestFactory = new DiagnosticHypothesisRequestFactory();
    }

    public function endpoints(): array
    {
        return [
            'POST' => ['api/diagnostic-hypothesis'],
            'PUT' => [
                'api/diagnostic-hypothesis/1',
                'api/diagnostic-hypothesis/change-status/1',
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

    #[DataProvider('invalidPayloads')]
    public function test_create_diagnostic_hypothesis_rule($invalidPayloads, $error)
    {
        Sanctum::actingAs($this->superAdminUser);

        $response = $this->postJson('api/diagnostic-hypothesis', DiagnosticHypothesisRequestFactory::new($invalidPayloads())->create());

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors($error());
    }

    public function test_user_can_create_diagnostic_hypothesis_with_valid_data()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $payload = DiagnosticHypothesisRequestFactory::new()->create();

        // Act
        $response = $this->postJson('api/diagnostic-hypothesis', $payload);

        // Assert
        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'name',
        ]);
        $response->assertJsonFragment([
            'name' => $payload['name'],
        ]);

        $this->assertDatabaseHas('diagnostic_hypotheses', [
            'name' => $payload['name'],
        ]);

        foreach ($payload['nature_types_id'] as $natureTypeId) {
            $this->assertDatabaseHas('nature_diagnostic_hypotheses', [
                'diagnostic_hypothesis_id' => $response->json('id'),
                'nature_type_id' => $natureTypeId,
            ]);
        }
    }

    public function test_user_can_show_all_diagnostic_hypotheses()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);

        // Act
        $response = $this->getJson('api/diagnostic-hypothesis');

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

        $results = collect($response['results']);

        $this->assertEquals($results->pluck('id')->max(), 10);

        $response->assertJsonMissing([
            'id' => 11,
        ]);
    }

    public function test_user_can_update_diagnostic_hypothesis_with_valid_data()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $payload = DiagnosticHypothesisRequestFactory::new()->create();

        // Act
        $response = $this->putJson('api/diagnostic-hypothesis/1', $payload);

        // Assert
        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'name',
            'is_active',
        ]);
        $response->assertJsonFragment([
            'name' => $payload['name'],
        ]);

        $this->assertDatabaseHas('diagnostic_hypotheses', [
            'name' => $payload['name'],
        ]);

        foreach ($payload['nature_types_id'] as $natureTypeId) {
            $this->assertDatabaseHas('nature_diagnostic_hypotheses', [
                'diagnostic_hypothesis_id' => $response->json('id'),
                'nature_type_id' => $natureTypeId,
            ]);
        }
    }

    public function test_user_can_change_diagnostic_hypothesis_status()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);

        // Act
        $response = $this->putJson('api/diagnostic-hypothesis/change-status/1');

        // Assert
        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'name',
            'is_active',
        ]);

        $response->assertJsonFragment([
            'is_active' => false,
        ]);
    }

    public function test_user_can_update_only_natures_with_valid_data()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);

        $diagnosticHypothesis = DiagnosticHypothesis::findOrFail(1);
        $natureTypes = [NatureTypeEnum::TRAUMA->value, NatureTypeEnum::CLINICAL->value];

        // Act
        $response = $this->putJson('api/diagnostic-hypothesis/1', [
            'name' => $diagnosticHypothesis['name'],
            'nature_types_id' => $natureTypes,
        ]);

        // Assert
        $response->assertOk();
        $response->assertJsonStructure([
            'id',
            'name',
            'is_active',
        ]);

        $response->assertJsonFragment([
            'name' => $diagnosticHypothesis['name'],
        ]);

        foreach ($natureTypes as $natureTypeId) {
            $this->assertDatabaseHas('nature_diagnostic_hypotheses', [
                'diagnostic_hypothesis_id' => $response->json('id'),
                'nature_type_id' => $natureTypeId,
            ]);
        }
    }

    public function test_user_can_filter_per_nature_type_or_name()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);

        // Act
        $response = $this->getJson('api/diagnostic-hypothesis?search=queda+de+altura&load_nature_types=1');

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

        $response->assertJsonCount(1, 'results');
        $response->assertJsonFragment([
            'name' => 'QUEDA DE ALTURA',
        ]);
    }

    public static function invalidPayloads()
    {
        yield 'name is required' => [
            fn () => DiagnosticHypothesisRequestFactory::new(['name' => null]),
            fn () => ['name' => __('request_messages/store_diagnostic_hypothesis.name.required')],
        ];

        yield 'name is string' => [
            fn () => DiagnosticHypothesisRequestFactory::new(['name' => 0]),
            fn () => ['name' => __('request_messages/store_diagnostic_hypothesis.name.string')],
        ];

        yield 'name is unique' => [
            fn () => DiagnosticHypothesisRequestFactory::new(['name' => 'QUEDA DE ALTURA']),
            fn () => ['name' => __('request_messages/store_diagnostic_hypothesis.name.unique')],
        ];

        yield 'nature_types_id is array' => [
            fn () => DiagnosticHypothesisRequestFactory::new(['nature_types_id' => 0]),
            fn () => ['nature_types_id' => __('request_messages/store_diagnostic_hypothesis.nature_types_id.array')],
        ];

        yield 'nature_types_id is required' => [
            fn () => DiagnosticHypothesisRequestFactory::new(['nature_types_id' => null]),
            fn () => ['nature_types_id' => __('request_messages/store_diagnostic_hypothesis.nature_types_id.required')],
        ];

        yield 'nature_types_id is valid enum case' => [
            fn () => DiagnosticHypothesisRequestFactory::new(['nature_types_id' => [1, 8]]),
            fn () => ['nature_types_id.1' => __('request_messages/store_diagnostic_hypothesis.nature_types_id.*.enum')],
        ];
    }
}
