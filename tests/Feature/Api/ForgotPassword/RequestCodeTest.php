<?php

namespace Tests\Feature\Api\ForgotPassword;

use App\Models\PasswordResetCode;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestCodeTest extends TestCase
{
    use RefreshDatabase;

    private string $url = '/api/auth/forgot-password/request-code';

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'country_code' => '+962',
            'phone_number' => '599999991',
            'full_phone'   => '0599999991',
        ], $overrides);
    }

    private function activeDriver(): User
    {
        return User::factory()->create([
            'phone'  => '0599999991',
            'role'   => 'driver',
            'status' => 'active',
        ]);
    }

    // ------------------------------------------------------------------
    // Success path
    // ------------------------------------------------------------------

    public function test_valid_phone_returns_success(): void
    {
        $this->mock(SmsService::class)
            ->shouldReceive('sendPasswordResetCode')
            ->once();

        $this->activeDriver();

        $this->postJson($this->url, $this->payload())
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => ['expires_in_seconds' => 300],
            ]);
    }

    public function test_generates_code_record_in_database(): void
    {
        $this->mock(SmsService::class)
            ->shouldReceive('sendPasswordResetCode')
            ->once();

        $user = $this->activeDriver();

        $this->postJson($this->url, $this->payload());

        $this->assertDatabaseHas('password_reset_codes', [
            'user_id' => $user->id,
            'phone'   => '0599999991',
            'used_at' => null,
        ]);
    }

    public function test_requesting_new_code_invalidates_previous_code(): void
    {
        $this->mock(SmsService::class)
            ->shouldReceive('sendPasswordResetCode')
            ->twice();

        $user = $this->activeDriver();

        // First request
        $this->postJson($this->url, $this->payload());
        $first = PasswordResetCode::where('user_id', $user->id)->latest()->first();
        $this->assertTrue($first->expires_at->isFuture());

        // Second request should expire the first code
        $this->postJson($this->url, $this->payload());
        $this->assertFalse($first->fresh()->expires_at->isFuture());
    }

    public function test_debug_code_not_present_in_testing_environment(): void
    {
        $this->mock(SmsService::class)
            ->shouldReceive('sendPasswordResetCode')
            ->once();

        $this->activeDriver();

        $this->postJson($this->url, $this->payload())
            ->assertStatus(200)
            ->assertJsonMissing(['debug_code']);
    }

    // ------------------------------------------------------------------
    // Account enumeration protection
    // ------------------------------------------------------------------

    public function test_unknown_phone_returns_generic_success_without_sms(): void
    {
        $this->mock(SmsService::class)
            ->shouldNotReceive('sendPasswordResetCode');

        $this->postJson($this->url, $this->payload())
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_admin_role_returns_generic_success_without_sms(): void
    {
        $this->mock(SmsService::class)
            ->shouldNotReceive('sendPasswordResetCode');

        User::factory()->create(['phone' => '0599999991', 'role' => 'admin', 'status' => 'active']);

        $this->postJson($this->url, $this->payload())
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_superadmin_role_returns_generic_success_without_sms(): void
    {
        $this->mock(SmsService::class)
            ->shouldNotReceive('sendPasswordResetCode');

        User::factory()->create(['phone' => '0599999991', 'role' => 'superadmin', 'status' => 'active']);

        $this->postJson($this->url, $this->payload())
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // ------------------------------------------------------------------
    // Inactive account errors
    // ------------------------------------------------------------------

    public function test_suspended_account_returns_403(): void
    {
        $this->mock(SmsService::class)
            ->shouldNotReceive('sendPasswordResetCode');

        User::factory()->create(['phone' => '0599999991', 'role' => 'driver', 'status' => 'suspended']);

        $this->postJson($this->url, $this->payload())
            ->assertStatus(403)
            ->assertJson(['code' => 'ACCOUNT_SUSPENDED']);
    }

    public function test_pending_account_returns_403(): void
    {
        $this->mock(SmsService::class)
            ->shouldNotReceive('sendPasswordResetCode');

        User::factory()->create(['phone' => '0599999991', 'role' => 'driver', 'status' => 'pending']);

        $this->postJson($this->url, $this->payload())
            ->assertStatus(403)
            ->assertJson(['code' => 'ACCOUNT_PENDING']);
    }

    // ------------------------------------------------------------------
    // Validation
    // ------------------------------------------------------------------

    public function test_missing_phone_number_returns_422(): void
    {
        $this->postJson($this->url, ['country_code' => '+962'])
            ->assertStatus(422)
            ->assertJson(['success' => false])
            ->assertJsonValidationErrors(['phone_number']);
    }

    public function test_missing_country_code_returns_422(): void
    {
        $this->postJson($this->url, ['phone_number' => '599999991'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);
    }
}
