<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function testUserCanLogin(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSuccessful()
            ->assertJsonStructure([
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);

    }

    public function testUserCannotLoginWithWrongCredentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('api.login'), [
            'email' => $user->email,
            'password' => 'wrong password',
        ]);

        $response->assertStatus(403)
            ->assertJsonStructure([
                'message',
                'status',
            ])->assertJsonFragment([
                'message' => 'Invalid credentials!',
            ]);

    }

    public function testFieldsAreValidated(): void
    {
        $response = $this->postJson(route('api.login'));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email','password'
            ]);
    }

    public function testUserCanLogout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('api.logout'))
        ->assertSuccessful()
        ->assertJsonFragment([
            'message' => 'Signed out!',
        ]);

        $this->assertSame(count($user->tokens), 0);
    }
}
