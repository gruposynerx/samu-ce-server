<?php

namespace Tests\Feature\User;

use App\Models\Role;
use App\Models\UrgencyRegulationCenter;
use App\Models\User;
use Database\Seeders\CitySeeder;
use Database\Seeders\DefaultUserSeeder;
use Database\Seeders\FederalUnitSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\UrgencyRegulationCentersSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(FederalUnitSeeder::class);
        $this->seed(CitySeeder::class);
        $this->seed(UrgencyRegulationCentersSeeder::class);
        //        $this->seed(DefaultUserSeeder::class);
    }

    /** @test */
    public function it_should_forbid_an_unauthenticated_user_can_create_other_users(): void
    {
        $user = User::factory()->raw();

        $response = $this->postJson(route('users.store'), [
            $user,
            'password_confirmation',
        ]);

        $response->assertUnauthorized();
        $this->assertDatabaseMissing(User::class, $user);
    }

    /** @test */
    public function it_should_forbid_an_user_create_other_users_without_permission(): void
    {
        $urc = UrgencyRegulationCenter::inRandomOrder()->first();

        $roles = Role::whereNotIn('name', ['super-admin', 'admin'])->pluck('name')->toArray();

        $user = User::factory()->raw();

        $authUser = User::factory()->create([
            'urc_id' => $urc->id,
            'current_role' => 'admin',
        ]);

        $authUser->assignRole($roles, $urc->id);

        Sanctum::actingAs($authUser);

        $request = [
            ...$user,
            'password_confirmation' => 'password',
            'profiles' => [
                'urc_id' => $urc->id,
                'role_id' => $roles[0],
            ],
        ];

        foreach ($roles as $role) {
            $authUser->update(['current_role' => $role]);

            $response = $this->postJson(route('users.store'), $request);

            $response->assertForbidden();
            $this->assertDatabaseMissing(User::class, $user);
        }
    }
}
