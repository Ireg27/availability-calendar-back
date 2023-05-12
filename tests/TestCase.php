<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->plainTextToken;

        $this->actingAs($user);
        $this->withHeaders(['Authorization' => 'Bearer ' . $token]);
    }
}
