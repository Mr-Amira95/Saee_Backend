<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReferenceApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $clientUser1;
    private ClientProfile $clientProfile1;
    private User $clientUser2;
    private ClientProfile $clientProfile2;
    private User $driverUser1;
    private DriverProfile $driverProfile1;
    private User $driverUser2;
    private DriverProfile $driverProfile2;
    private City $city;
    private Area $area;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(\App\Services\SupportNotificationService::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        });

        $this->orderService = app(OrderService::class);

        // Create Admin
        $this->admin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        // Create City & Area
        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        // Create Client 1
        $this->clientUser1 = User::factory()->create([
            'role' => 'client_master',
            'status' => 'active',
        ]);
        $this->clientProfile1 = ClientProfile::create([
            'master_user_id' => $this->clientUser1->id,
            'company_name' => 'Merchant One',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);

        // Create Client 2
        $this->clientUser2 = User::factory()->create([
            'role' => 'client_master',
            'status' => 'active',
        ]);
        $this->clientProfile2 = ClientProfile::create([
            'master_user_id' => $this->clientUser2->id,
            'company_name' => 'Merchant Two',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);

        // Create Driver 1
        $this->driverUser1 = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
        ]);
        $this->driverProfile1 = DriverProfile::create([
            'user_id' => $this->driverUser1->id,
            'national_id' => '1111111111',
            'license_number' => 'L-1111',
            'license_expiry_date' => now()->addYear(),
        ]);

        // Create Driver 2
        $this->driverUser2 = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
        ]);
        $this->driverProfile2 = DriverProfile::create([
            'user_id' => $this->driverUser2->id,
            'national_id' => '2222222222',
            'license_number' => 'L-2222',
            'license_expiry_date' => now()->addYear(),
        ]);
    }

    public function test_authorized_client_can_retrieve_order_by_reference_in_path(): void
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile1->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver 1',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $response = $this->actingAs($this->clientUser1)
            ->getJson(route('api.orders.by-reference', ['reference_code' => $order->order_number]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order retrieved successfully.',
                'order_id' => $order->id,
            ])
            ->assertJsonPath('data.order_number', $order->order_number);
    }

    public function test_authorized_client_can_retrieve_order_by_reference_in_query_param(): void
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile1->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver 1',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $response = $this->actingAs($this->clientUser1)
            ->getJson(route('api.orders.by-reference') . '?reference_code=' . $order->order_number);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order retrieved successfully.',
                'order_id' => $order->id,
            ])
            ->assertJsonPath('data.order_number', $order->order_number);
    }

    public function test_unauthorized_client_cannot_retrieve_other_clients_order(): void
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile1->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver 1',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        // Attempt as Client 2
        $response = $this->actingAs($this->clientUser2)
            ->getJson(route('api.orders.by-reference', ['reference_code' => $order->order_number]));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You do not have access to this order.',
            ]);
    }

    public function test_authorized_driver_can_retrieve_order_by_reference(): void
    {
        // Create order and assign it to driver 1
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile1->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver 1',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $order->update(['driver_profile_id' => $this->driverProfile1->id]);

        $response = $this->actingAs($this->driverUser1)
            ->getJson(route('api.orders.by-reference', ['reference_code' => $order->order_number]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'order_id' => $order->id,
            ]);
    }

    public function test_unauthorized_driver_cannot_retrieve_unassigned_order(): void
    {
        // Order assigned to driver 1
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile1->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver 1',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $order->update(['driver_profile_id' => $this->driverProfile1->id]);

        // Attempt as driver 2 (unassigned)
        $response = $this->actingAs($this->driverUser2)
            ->getJson(route('api.orders.by-reference', ['reference_code' => $order->order_number]));

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You do not have access to this order.',
            ]);
    }

    public function test_admin_user_can_retrieve_any_order(): void
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile1->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver 1',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $response = $this->actingAs($this->admin)
            ->getJson(route('api.orders.by-reference', ['reference_code' => $order->order_number]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'order_id' => $order->id,
            ]);
    }

    public function test_non_existent_reference_returns_404(): void
    {
        $response = $this->actingAs($this->clientUser1)
            ->getJson(route('api.orders.by-reference', ['reference_code' => 'INVALID_REF']));

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Order not found.',
            ]);
    }

    public function test_missing_reference_returns_422(): void
    {
        $response = $this->actingAs($this->clientUser1)
            ->getJson(route('api.orders.by-reference'));

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Reference code is required.',
            ]);
    }
}
