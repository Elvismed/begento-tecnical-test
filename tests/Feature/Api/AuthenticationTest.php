<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'admin@begento.test',
            'password' => 'password',
        ], $attributes));
    }

    public function test_returns_token_with_valid_credentials(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/token', [
            'email' => 'admin@begento.test',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type'])
            ->assertJsonPath('token_type', 'Bearer');

        $this->assertStringContainsString(
            (string) config('sanctum.token_prefix'),
            $response->json('token'),
        );

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'api',
        ]);
    }

    public function test_rejects_invalid_password_with_401(): void
    {
        $this->createUser();

        $this->postJson('/api/auth/token', [
            'email' => 'admin@begento.test',
            'password' => 'wrong-password',
        ])->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_rejects_unknown_email_with_401(): void
    {
        $this->postJson('/api/auth/token', [
            'email' => 'unknown@begento.test',
            'password' => 'password',
        ])->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_rejects_invalid_login_payload_with_422(): void
    {
        $this->postJson('/api/auth/token', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_limits_login_attempts(): void
    {
        config(['api.rate_limit.login' => 5]);

        $this->createUser();

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/api/auth/token', [
                'email' => 'admin@begento.test',
                'password' => 'wrong-password',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/auth/token', [
            'email' => 'admin@begento.test',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    public function test_returns_authenticated_user(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@begento.test')
            ->assertJsonMissingPath('data.password');
    }

    public function test_rejects_request_without_token(): void
    {
        $this->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.')
            ->assertHeader('WWW-Authenticate', 'Bearer');
    }

    public function test_revokes_current_token_on_logout(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Token revoked.');

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_rejects_expired_token(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('expired', ['*'], now()->subMinute())->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_returns_security_headers(): void
    {
        $response = $this->postJson('/api/auth/token', [
            'email' => 'unknown@begento.test',
            'password' => 'password',
        ]);

        $response->assertUnauthorized()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'no-referrer');

        $this->assertStringContainsString(
            'no-store',
            (string) $response->headers->get('Cache-Control'),
        );
    }
}
