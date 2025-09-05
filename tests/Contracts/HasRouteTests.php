<?php

namespace Tests\Contracts;

interface HasRouteTests
{
    public function test_role_has_correct_access(array $role, bool $canAccess);

    public function endpoints(): array;
}
