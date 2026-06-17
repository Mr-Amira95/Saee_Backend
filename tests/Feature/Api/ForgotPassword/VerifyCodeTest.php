<?php

namespace Tests\Feature\Api\ForgotPassword;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class VerifyCodeTest extends TestCase
{
    use RefreshDatabase;

    private string $url = '/api/auth/forgot-password/verify-code';

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'country_code' => '+962',
            'phone_number' => '599999991',
            'full_phone'   => '0599999991',
            'code'         => '123456',
        ], $overrides);
    }

    private function createUserWithCode(array $codeAttrs = []): array
    {
        $user = User::factory()->create(['phone' => '0599999991']);

        $code = '123456';
        $resetCode = PasswordResetCode::create(array_merge([
            'user_id'    => $user->id,
            'phone'      => $user->phone,
            'code_hash'  => Hash::make($code),
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(5),
        ], $codeAttrs));

        return [$user, $resetCode, $code];
    }

    // ------------------------------------------------------------------
    // Success path
    // ------------------------------------------------------------------

    public function test_correct_code_returns_reset_token(): void
    {
        [$user, $resetCode, $code] = $this->createUserWithCode();

        $this->postJson($this->url, $this->payload(['code' => $code]))
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['reset_token', 'expires_in_seconds']]);
    }

    public function test_verify_sets_verified_at_and_stores_token_hash(): void
    {
        [$user, $resetCode, $code] = $this->createUserWithCode();

        $this->postJson($this->url, $this->payload(['code' => $code]));

        $resetCode->refresh();
        $this->assertNotNull($resetCode->verified_at);
        $this->assertNotNull($resetCode->reset_token_hash);
        $this->assertNotNull($resetCode->reset_token_expires_at);
    }

    // ------------------------------------------------------------------
    // Wrong / expired code
    // ------------------------------------------------------------------

    public function test_wrong_code_returns_400(): void
    {
        $this->createUserWithCode();

        $this->postJson($this->url, $this->payload(['code' => '000000']))
            ->assertStatus(400)
            ->assertJson(['code' => 'INVALID_OR_EXPIRED_CODE']);
    }

    public function test_wrong_code_increments_attempts(): void
    {
        [$user, $resetCode] = $this->createUserWithCode();

        $this->postJson($this->url, $this->payload(['code' => '000000']));

        $this->assertEquals(1, $resetCode->fresh()->attempts);
    }

    public function test_expired_code_returns_400(): void
    {
        $this->createUserWithCode(['expires_at' => now()->subSecond()]);

        $this->postJson($this->url, $this->payload())
            ->assertStatus(400)
            ->assertJson(['code' => 'INVALID_OR_EXPIRED_CODE']);
    }

    public function test_used_code_is_ignored(): void
    {
        $this->createUserWithCode(['used_at' => now()]);

        $this->postJson($this->url, $this->payload())
            ->assertStatus(400)
            ->assertJson(['code' => 'INVALID_OR_EXPIRED_CODE']);
    }

    // ------------------------------------------------------------------
    // Max attempts
    // ------------------------------------------------------------------

    public function test_too_many_attempts_returns_429(): void
    {
        $this->createUserWithCode(['attempts' => 5]);

        $this->postJson($this->url, $this->payload())
            ->assertStatus(429)
            ->assertJson(['code' => 'TOO_MANY_ATTEMPTS']);
    }

    public function test_exactly_five_attempts_blocks_without_checking_code(): void
    {
        [$user, $resetCode, $code] = $this->createUserWithCode(['attempts' => 5]);

        // Even with the correct code, blocked at 5 attempts
        $this->postJson($this->url, $this->payload(['code' => $code]))
            ->assertStatus(429)
            ->assertJson(['code' => 'TOO_MANY_ATTEMPTS']);
    }

    // ------------------------------------------------------------------
    // Unknown phone
    // ------------------------------------------------------------------

    public function test_unknown_phone_returns_400(): void
    {
        $this->postJson($this->url, $this->payload())
            ->assertStatus(400)
            ->assertJson(['code' => 'INVALID_OR_EXPIRED_CODE']);
    }

    // ------------------------------------------------------------------
    // Validation
    // ------------------------------------------------------------------

    public function test_five_digit_code_fails_validation(): void
    {
        $this->postJson($this->url, $this->payload(['code' => '12345']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_seven_digit_code_fails_validation(): void
    {
        $this->postJson($this->url, $this->payload(['code' => '1234567']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_non_numeric_code_fails_validation(): void
    {
        $this->postJson($this->url, $this->payload(['code' => 'abc123']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_missing_code_returns_422(): void
    {
        $payload = $this->payload();
        unset($payload['code']);

        $this->postJson($this->url, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }
}
