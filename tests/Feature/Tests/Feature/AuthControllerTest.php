<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUserRegistration()
    {
        $userData = [
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'passwordConfirmation' => 'password',
        ];

        $res = $this->post('/api/register', $userData);

        $res->assertStatus(201)
            ->assertJson([
                'message' => 'User registration successful.',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);
    }

    public function testUserLogin()
    {
        User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => bcrypt('password'),
        ]);

        $credentials = [
            'email' => 'johndoe@example.com',
            'password' => 'password'
        ];

        $res = $this->post('/api/login', $credentials);

        $res->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'access_token',
                    'user_id',
                ],
                'message'
            ]);

        $this->assertAuthenticated();
    }
}
