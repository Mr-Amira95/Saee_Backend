<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\Order;
use App\Models\User;
use App\Models\Attendance;
use App\Models\RejectionReason;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DriverStatusNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private User $driverUser;
    private DriverProfile $driverProfile;
    private User $clientUser;
    private ClientProfile $clientProfile;
    private City $city;
    private Area $area;
    private OrderService $orderService;
    private RejectionReason $rejectionReason;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);

        // Create Admin
        $this->admin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'phone' => '0790000991',
        ]);

        // Create City & Area
        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        // Create Client
        $this->clientUser = User::factory()->create([
            'role' => 'client_master',
            'status' => 'active',
            'phone' => '0790000992',
        ]);
        $this->clientProfile = ClientProfile::create([
            'master_user_id' => $this->clientUser->id,
            'company_name' => 'Merchant Test',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);

        // Create Driver
        $this->driverUser = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
            'phone' => '0790000993',
        ]);
        $this->driverProfile = DriverProfile::create([
            'user_id' => $this->driverUser->id,
            'national_id' => '1111111111',
            'license_number' => 'L-1111',
            'license_expiry_date' => now()->addYear(),
        ]);

        // Create Rejection Reason
        $this->rejectionReason = RejectionReason::create([
            'reason' => 'Customer did not answer',
            'is_active' => true,
        ]);
    }

    public function test_order_rejection_triggers_notification_to_client(): void
    {
        // 1. Create order
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->clientProfile->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Test',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 St',
        ], $this->admin);

        // 2. Assign and transition to picked_up
        $order->update([
            'driver_profile_id' => $this->driverProfile->id,
            'status' => 'picked_up',
        ]);

        // 3. Check in the driver
        Attendance::create([
            'user_id' => $this->driverUser->id,
            'date' => today(),
            'check_in_at' => now(),
        ]);

        // 4. Reject the order as driver
        $response = $this->actingAs($this->driverUser)
            ->postJson("/api/orders/{$order->id}/reject", [
                'rejection_reason_id' => $this->rejectionReason->id,
                'notes' => 'Called 3 times.',
            ]);

        $response->assertStatus(200);

        // 5. Assert the order status is updated
        $this->assertEquals('rejected', $order->fresh()->status);

        // 6. Assert a SystemNotification was created for the client master
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->clientUser->id,
            'title' => 'Order Rejected',
            'message' => "Your order #{$order->order_number} has been rejected.",
            'entity_type' => 'single_order',
            'entity_id' => $order->id,
        ]);
    }
}
