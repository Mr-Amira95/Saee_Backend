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

class DriverHomeApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $driverUser;
    private DriverProfile $driverProfile;
    private ClientProfile $clientProfile;
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
            'phone' => '0790000881',
        ]);

        // Create City & Area
        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        // Create Client
        $clientUser = User::factory()->create([
            'role' => 'client_master',
            'status' => 'active',
            'phone' => '0790000882',
        ]);
        $this->clientProfile = ClientProfile::create([
            'master_user_id' => $clientUser->id,
            'company_name' => 'Merchant Test',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);

        // Create Driver
        $this->driverUser = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
            'phone' => '0790000883',
        ]);
        $this->driverProfile = DriverProfile::create([
            'user_id' => $this->driverUser->id,
            'national_id' => '1111111111',
            'license_number' => 'L-1111',
            'license_expiry_date' => now()->addYear(),
        ]);
    }

    public function test_driver_home_api_returns_assigned_orders_count(): void
    {
        // 1. Create 3 orders
        $order1 = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver 1',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $order2 = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver 2',
            'receiver_phone' => '0790000002',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $order3 = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver 3',
            'receiver_phone' => '0790000003',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        // 2. Assign orders to driver:
        // Order 1 -> assigned
        // Order 2 -> picked_up
        // Order 3 -> assigned (assigned status)
        $order1->update([
            'driver_profile_id' => $this->driverProfile->id,
            'status' => 'assigned',
        ]);
        $order2->update([
            'driver_profile_id' => $this->driverProfile->id,
            'status' => 'picked_up',
        ]);
        $order3->update([
            'driver_profile_id' => $this->driverProfile->id,
            'status' => 'assigned',
        ]);

        // 3. Make GET request to Driver Home API
        $response = $this->actingAs($this->driverUser)
            ->getJson(route('api.home'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_orders' => 1, // Only status = picked_up is counted as total_orders
                        'assigned_orders' => 2, // status = assigned orders
                    ]
                ]
            ]);
    }
}
