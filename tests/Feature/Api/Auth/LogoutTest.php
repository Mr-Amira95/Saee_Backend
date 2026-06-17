<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role'   => 'driver',
            'status' => 'active',
        ]);
    }

    public function test_logout_without_token_returns_401(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Unauthenticated',
                     'code'    => 'UNAUTHENTICATED',
                 ]);
    }

    public function test_logout_with_invalid_token_returns_401(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-garbage-token',
        ])->postJson('/api/auth/logout');

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Unauthenticated',
                     'code'    => 'UNAUTHENTICATED',
                 ]);
    }

    public function test_logout_with_valid_token_returns_200(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Logged out successfully',
                 ]);
    }

    public function test_token_cannot_be_used_after_logout(): void
    {
        $token = $this->user->createToken('mobile')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
             ->postJson('/api/auth/logout')
             ->assertStatus(200);

        // Reset cached auth guard so the next request re-queries the DB
        auth()->forgetGuards();

        // Same token must now be rejected
        $this->withHeaders(['Authorization' => "Bearer {$token}"])
             ->postJson('/api/auth/logout')
             ->assertStatus(401)
             ->assertJson(['code' => 'UNAUTHENTICATED']);
    }

    public function test_other_tokens_for_same_user_remain_valid(): void
    {
        $tokenA = $this->user->createToken('device-a')->plainTextToken;
        $tokenB = $this->user->createToken('device-b')->plainTextToken;

        // Logout with token A
        $this->withHeaders(['Authorization' => "Bearer {$tokenA}"])
             ->postJson('/api/auth/logout')
             ->assertStatus(200);

        // Token B must still be accepted
        $this->withHeaders(['Authorization' => "Bearer {$tokenB}"])
             ->postJson('/api/auth/logout')
             ->assertStatus(200);
    }
}
