<?php

namespace Tests\Feature;

use App\Enums\RolesEnum;
use App\Models\Base;
use App\Traits\TestsRoles;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\Contracts\HasRouteTests;
use Tests\RequestFactories\BaseRequestFactory;
use Tests\TestCase;

class BasesTest extends TestCase implements HasRouteTests
{
    use DatabaseTransactions, TestsRoles;

    public array $testEndpoints;

    public $requestFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->requestFactory = new BaseRequestFactory();
    }

    public function endpoints(): array
    {
        $base = Base::factory()->create();

        return [
            'GET' => ["/api/bases/$base->id"],
            'POST' => ['api/bases'],
            'PUT' => [
                "api/bases/$base->id",
                "api/bases/change-status/$base->id",
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

    public function test_user_can_create_bases_with_valid_payload()
    {
        // Arrange
        Sanctum::actingAs($this->superAdminUser);
        $payload = BaseRequestFactory::new()->create();

        // Act
        $response = $this->postJson('/api/bases', $payload);

        // Assert
        $response->assertCreated();
        $response->assertJsonStructure(
            array_merge(
                array_keys($payload),
                ['id', 'created_at', 'updated_at', 'is_active']
            )
        );

        $this->assertDatabaseHas('bases', $payload);
    }
}
