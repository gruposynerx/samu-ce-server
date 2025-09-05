<?php

namespace App\Traits;

use App\Models\Role;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;

trait TestsRoles
{
    #[DataProvider('availableRoles')]
    public function test_role_has_correct_access($role, $canAccess)
    {
        $endpoints = $this->endpoints();

        if (!isset($endpoints) || !isset($this->requestFactory)) {
            throw new \Exception('You must define the endpoints and requestFactory properties in the test class.');
        }

        $role = Role::where('name', $role)->first();

        $user = $this->superAdminUser;

        $user->update(['current_role' => $role->id]);
        $user->syncRoles([$role->name]);
        $user->refresh();
        Sanctum::actingAs($user);

        foreach ($endpoints as $method => $route) {
            foreach ($route as $r) {
                $response = $this->json($method, $r, $this->requestFactory->create());

                if ($canAccess) {
                    $response->assertSuccessful();
                } else {
                    $response->assertForbidden();
                }
            }
        }
    }
}
