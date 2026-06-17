<?php

namespace Tests\Feature\Api\ForgotPassword;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    private string $url = '/api/auth/forgot-password/reset';

    private function validPayload(string $resetToken, array $overrides = []): array
    {
        return array_merge([
            'reset_token'          => $resetToken,
            'password'             => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ], $overrides);
    }

    private function createVerifiedResetCode(array $userAttrs = [], array $codeAttrs = []): array
    {
        $user = User::factory()->create(array_merge(['status' => 'active'], $userAttrs));

        $resetToken = 'test_token_' . Str::random(32);

        $resetCode = PasswordResetCode::create(array_merge([
            'user_id'                => $user->id,
            'phone'                  => $user->phone ?? '0599999991',
            'code_hash'              => Hash::make('123456'),
            'reset_token_hash'       => hash('sha256', $resetToken),
            'attempts'               => 0,
            'verified_at'            => now(),
            'used_at'                => null,
            'expires_at'             => now()->addMinutes(5),
            'reset_token_expires_at' => now()->addMinutes(10),
        ], $codeAttrs));

        return [$user, $resetCode, $resetToken];
    }

    // ------------------------------------------------------------------
    // Success path
    // ------------------------------------------------------------------

    public function test_valid_token_resets_password(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode();

        $this->postJson($this->url, $this->validPayload($resetToken))
            ->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Password reset successfully']);
    }

    public function test_password_is_actually_updated_in_database(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode();

        $this->postJson($this->url, $this->validPayload($resetToken));

        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password));
    }

    public function test_reset_code_is_marked_as_used(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode();

        $this->postJson($this->url, $this->validPayload($resetToken));

        $this->assertNotNull($resetCode->fresh()->used_at);
    }

    public function test_sanctum_tokens_are_revoked_after_reset(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode();
        $user->createToken('mobile');

        $this->assertCount(1, $user->tokens()->get());

        $this->postJson($this->url, $this->validPayload($resetToken));

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
    }

    public function test_used_token_cannot_be_reused(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode();

        $this->postJson($this->url, $this->validPayload($resetToken))->assertStatus(200);
        $this->postJson($this->url, $this->validPayload($resetToken))->assertStatus(400);
    }

    // ------------------------------------------------------------------
    // Invalid / expired token
    // ------------------------------------------------------------------

    public function test_invalid_token_returns_400(): void
    {
        $this->postJson($this->url, $this->validPayload('completely_wrong_token'))
            ->assertStatus(400)
            ->assertJson(['code' => 'INVALID_OR_EXPIRED_RESET_TOKEN']);
    }

    public function test_expired_reset_token_returns_400(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode([], [
            'reset_token_expires_at' => now()->subSecond(),
        ]);

        $this->postJson($this->url, $this->validPayload($resetToken))
            ->assertStatus(400)
            ->assertJson(['code' => 'INVALID_OR_EXPIRED_RESET_TOKEN']);
    }

    public function test_unverified_code_token_returns_400(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode([], [
            'verified_at' => null,
        ]);

        $this->postJson($this->url, $this->validPayload($resetToken))
            ->assertStatus(400)
            ->assertJson(['code' => 'INVALID_OR_EXPIRED_RESET_TOKEN']);
    }

    // ------------------------------------------------------------------
    // Inactive account
    // ------------------------------------------------------------------

    public function test_suspended_account_returns_403(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode(['status' => 'suspended']);

        $this->postJson($this->url, $this->validPayload($resetToken))
            ->assertStatus(403)
            ->assertJson(['code' => 'ACCOUNT_SUSPENDED']);
    }

    // ------------------------------------------------------------------
    // Password validation
    // ------------------------------------------------------------------

    public function test_password_too_short_returns_422(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode();

        $this->postJson($this->url, $this->validPayload($resetToken, [
            'password'              => 'Ab1',
            'password_confirmation' => 'Ab1',
        ]))->assertStatus(422)
           ->assertJsonValidationErrors(['password']);
    }

    public function test_password_without_letter_returns_422(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode();

        $this->postJson($this->url, $this->validPayload($resetToken, [
            'password'              => '12345678',
            'password_confirmation' => '12345678',
        ]))->assertStatus(422)
           ->assertJsonValidationErrors(['password']);
    }

    public function test_password_without_number_returns_422(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode();

        $this->postJson($this->url, $this->validPayload($resetToken, [
            'password'              => 'abcdefgh',
            'password_confirmation' => 'abcdefgh',
        ]))->assertStatus(422)
           ->assertJsonValidationErrors(['password']);
    }

    public function test_password_confirmation_mismatch_returns_422(): void
    {
        [$user, $resetCode, $resetToken] = $this->createVerifiedResetCode();

        $this->postJson($this->url, $this->validPayload($resetToken, [
            'password'              => 'NewPassword1',
            'password_confirmation' => 'DifferentPassword1',
        ]))->assertStatus(422)
           ->assertJsonValidationErrors(['password']);
    }

    public function test_missing_reset_token_returns_422(): void
    {
        $this->postJson($this->url, [
            'password'              => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['reset_token']);
    }

    public function test_missing_password_returns_422(): void
    {
        $this->postJson($this->url, ['reset_token' => 'some_token'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
