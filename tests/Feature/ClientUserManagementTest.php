<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientEmployee;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $masterUser;
    private ClientProfile $clientProfile;
    private City $city;
    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        $this->masterUser = User::factory()->create([
            'role' => 'client_master',
            'name' => 'Master Client',
            'status' => 'active',
            'phone' => '0799999991',
            'email' => 'master@example.com',
        ]);
        $this->clientProfile = ClientProfile::create([
            'master_user_id' => $this->masterUser->id,
            'company_name' => 'Test Company',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);
    }

    public function test_non_master_user_cannot_access_user_management(): void
    {
        $employeeUser = User::factory()->create([
            'role' => 'client_employee',
            'phone' => '0799999992',
            'email' => 'employee@example.com',
        ]);
        ClientEmployee::create([
            'user_id' => $employeeUser->id,
            'client_profile_id' => $this->clientProfile->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($employeeUser)
            ->get(route('client.users.index'));

        $response->assertStatus(403);
    }

    public function test_master_user_can_access_user_management(): void
    {
        $response = $this->actingAs($this->masterUser)
            ->get(route('client.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('client.users.index');
    }

    public function test_master_user_can_create_user(): void
    {
        $response = $this->actingAs($this->masterUser)
            ->get(route('client.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('client.users.create');

        $payload = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0791234567',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'job_title' => 'Manager',
        ];

        $response = $this->actingAs($this->masterUser)
            ->post(route('client.users.store'), $payload);

        $response->assertRedirect(route('client.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '0791234567',
            'role' => 'client_employee',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertDatabaseHas('client_employees', [
            'user_id' => $user->id,
            'client_profile_id' => $this->clientProfile->id,
            'job_title' => 'Manager',
        ]);
    }

    public function test_master_user_can_edit_and_update_user(): void
    {
        $employeeUser = User::factory()->create([
            'role' => 'client_employee',
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'phone' => '0799999993',
        ]);
        $employee = ClientEmployee::create([
            'user_id' => $employeeUser->id,
            'client_profile_id' => $this->clientProfile->id,
            'job_title' => 'Old Title',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->masterUser)
            ->get(route('client.users.edit', $employee->id));

        $response->assertStatus(200);
        $response->assertViewIs('client.users.edit');

        $payload = [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '0791111111',
            'job_title' => 'New Title',
        ];

        $response = $this->actingAs($this->masterUser)
            ->put(route('client.users.update', $employee->id), $payload);

        $response->assertRedirect(route('client.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $employeeUser->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '0791111111',
        ]);

        $this->assertDatabaseHas('client_employees', [
            'id' => $employee->id,
            'job_title' => 'New Title',
        ]);
    }

    public function test_master_user_can_soft_delete_user(): void
    {
        $employeeUser = User::factory()->create([
            'role' => 'client_employee',
            'phone' => '0799999994',
            'email' => 'to-be-deleted@example.com',
        ]);
        $employee = ClientEmployee::create([
            'user_id' => $employeeUser->id,
            'client_profile_id' => $this->clientProfile->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->masterUser)
            ->delete(route('client.users.destroy', $employee->id));

        $response->assertRedirect(route('client.users.index'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('users', [
            'id' => $employeeUser->id,
        ]);

        $this->assertSoftDeleted('client_employees', [
            'id' => $employee->id,
        ]);
    }
}
