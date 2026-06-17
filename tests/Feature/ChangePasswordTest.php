<?php

namespace Tests\Feature;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/auth/change-password';

    private function activeUser(array $overrides = []): User
    {
        return User::factory()->create([
            'password' => 'OldPassword123',
            'status'   => 'active',
            'role'     => 'driver',
            ...$overrides,
        ]);
    }

    private function validPayload(): array
    {
        return [
            'current_password'      => 'OldPassword123',
            'password'              => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ];
    }

    // -----------------------------------------------------------------------
    // Authentication
    // -----------------------------------------------------------------------

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(401)
            ->assertJson(['success' => false, 'code' => 'UNAUTHENTICATED']);
    }

    // -----------------------------------------------------------------------
    // Validation failures
    // -----------------------------------------------------------------------

    public function test_current_password_is_required(): void
    {
        $user = $this->activeUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, [
                'password'              => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_new_password_is_required(): void
    {
        $user = $this->activeUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, [
                'current_password' => 'OldPassword123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $user = $this->activeUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, [
                'current_password'      => 'OldPassword123',
                'password'              => 'NewPassword123',
                'password_confirmation' => 'WrongConfirm1',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_must_be_at_least_8_characters(): void
    {
        $user = $this->activeUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, [
                'current_password'      => 'OldPassword123',
                'password'              => 'Ab1',
                'password_confirmation' => 'Ab1',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_must_contain_at_least_one_letter(): void
    {
        $user = $this->activeUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, [
                'current_password'      => 'OldPassword123',
                'password'              => '12345678',
                'password_confirmation' => '12345678',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_must_contain_at_least_one_number(): void
    {
        $user = $this->activeUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, [
                'current_password'      => 'OldPassword123',
                'password'              => 'NoNumbers!',
                'password_confirmation' => 'NoNumbers!',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_new_password_must_differ_from_current_password(): void
    {
        $user = $this->activeUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, [
                'current_password'      => 'OldPassword123',
                'password'              => 'OldPassword123',
                'password_confirmation' => 'OldPassword123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // -----------------------------------------------------------------------
    // Wrong current password
    // -----------------------------------------------------------------------

    public function test_wrong_current_password_returns_422(): void
    {
        $user = $this->activeUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, [
                'current_password'      => 'WrongPassword1',
                'password'              => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code'    => 'CURRENT_PASSWORD_INCORRECT',
            ])
            ->assertJsonPath('errors.current_password.0', 'Current password is incorrect.');
    }

    // -----------------------------------------------------------------------
    // Account status guards
    // -----------------------------------------------------------------------

    public function test_suspended_account_is_rejected(): void
    {
        $user = $this->activeUser(['status' => 'suspended']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'code' => 'ACCOUNT_SUSPENDED']);
    }

    public function test_pending_account_is_rejected(): void
    {
        $user = $this->activeUser(['status' => 'pending']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(403)
            ->assertJson(['success' => false, 'code' => 'ACCOUNT_PENDING']);
    }

    // -----------------------------------------------------------------------
    // Successful change
    // -----------------------------------------------------------------------

    public function test_correct_request_returns_200(): void
    {
        $user = $this->activeUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, $this->validPayload());

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Password changed successfully']);
    }

    public function test_password_hash_changes_in_database(): void
    {
        $user = $this->activeUser();
        $oldHash = $user->password;

        $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, $this->validPayload());

        $user->refresh();
        $this->assertNotEquals($oldHash, $user->password);
        $this->assertTrue(Hash::check('NewPassword123', $user->password));
    }

    public function test_old_password_no_longer_works(): void
    {
        $user = $this->activeUser();

        $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, $this->validPayload());

        $user->refresh();
        $this->assertFalse(Hash::check('OldPassword123', $user->password));
    }

    public function test_new_password_works_at_login(): void
    {
        $user = $this->activeUser(['phone' => '0791234567']);
        DriverProfile::create([
            'user_id'             => $user->id,
            'national_id'         => '9876543210',
            'license_number'      => 'L-9999',
            'license_expiry_date' => now()->addYear(),
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson(self::ENDPOINT, $this->validPayload());

        $response = $this->postJson('/api/auth/login', [
            'country_code' => '962',
            'phone_number' => '791234567',
            'password'     => 'NewPassword123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_other_tokens_are_revoked_after_change(): void
    {
        $user = $this->activeUser();

        // Create two extra tokens for other devices
        $user->createToken('device-a');
        $user->createToken('device-b');
        $currentToken = $user->createToken('current-device')->plainTextToken;

        $this->assertEquals(3, $user->tokens()->count());

        $this->withToken($currentToken)
            ->postJson(self::ENDPOINT, $this->validPayload())
            ->assertStatus(200);

        // Only the current token should remain; the two others are revoked
        $this->assertEquals(1, $user->tokens()->count());
    }
}
