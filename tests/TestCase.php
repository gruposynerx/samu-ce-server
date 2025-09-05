<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected ?User $superAdminUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminUser = User::whereIdentifier('12345678909')->first();
    }
}
