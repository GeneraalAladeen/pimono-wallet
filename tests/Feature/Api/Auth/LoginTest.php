<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_user_can_login(): void
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

    public function test_user_cannot_login_with_wrong_credentials(): void
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

    public function test_fields_are_validated(): void
    {
        $response = $this->postJson(route('api.login'));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email', 'password',
            ]);
    }

    public function test_user_can_logout(): void
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
