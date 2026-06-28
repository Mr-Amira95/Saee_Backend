<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $clientUser;
    private ClientProfile $clientProfile;
    private City $city;
    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        $this->clientUser = User::factory()->create([
            'role' => 'client_master',
            'status' => 'active',
        ]);

        $this->clientProfile = ClientProfile::create([
            'master_user_id' => $this->clientUser->id,
            'company_name' => 'Test Client Company',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);
    }

    public function test_client_dashboard_displays_correct_metrics(): void
    {
        // Order 1: Pending Cash (not delivered, not rejected, not returned, not cancelled, payment_status != paid)
        // Amount: order_amount (50) + customer_delivery_amount (5) = 55
        $order1 = Order::create([
            'client_profile_id' => $this->clientProfile->id,
            'status' => 'picked_up',
            'payment_status' => 'pending',
        ]);
        $order1->payment()->create([
            'payment_type' => 'cod',
            'order_amount' => 50.00,
            'delivery_on_customer' => true,
            'customer_delivery_amount' => 5.00,
            'client_delivery_amount' => 0.00,
        ]);

        // Order 2: Account Balance (delivered, payment_status != paid)
        // Amount: order_amount (100) + customer_delivery_amount (10) = 110
        $order2 = Order::create([
            'client_profile_id' => $this->clientProfile->id,
            'status' => 'delivered',
            'payment_status' => 'with_driver',
        ]);
        $order2->payment()->create([
            'payment_type' => 'cod',
            'order_amount' => 100.00,
            'delivery_on_customer' => true,
            'customer_delivery_amount' => 10.00,
            'client_delivery_amount' => 0.00,
        ]);

        // Order 3: Excluded from calculations (status: delivered, but payment_status = paid)
        $order3 = Order::create([
            'client_profile_id' => $this->clientProfile->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
        ]);
        $order3->payment()->create([
            'payment_type' => 'cod',
            'order_amount' => 150.00,
            'delivery_on_customer' => false,
            'customer_delivery_amount' => 0.00,
            'client_delivery_amount' => 5.00,
        ]);

        // Order 4: Excluded from calculations (status: cancelled, payment_status = pending)
        $order4 = Order::create([
            'client_profile_id' => $this->clientProfile->id,
            'status' => 'cancelled',
            'payment_status' => 'pending',
        ]);
        $order4->payment()->create([
            'payment_type' => 'cod',
            'order_amount' => 30.00,
            'delivery_on_customer' => false,
            'customer_delivery_amount' => 0.00,
            'client_delivery_amount' => 5.00,
        ]);

        // Access client dashboard
        $response = $this->actingAs($this->clientUser)->get(route('client.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('pendingCash', 55.0);
        $response->assertViewHas('balance', 110.0);
        $response->assertViewHas('daysTrend');
        
        $response->assertSee('Pending Cash');
        $response->assertSee('Account Balance');
        $response->assertSee('Shipping Volume');
        $response->assertSee('Last 7 Days');

        // Verify the 4 clickable cards and their links
        $response->assertSee(route('client.orders.index', ['status' => 'pending']));
        $response->assertSee(route('client.orders.index', ['status' => 'in_transit']));
        $response->assertSee(route('client.orders.index', ['status' => 'delivered', 'from' => now()->toDateString(), 'to' => now()->toDateString()]));
        $response->assertSee(route('client.orders.index', ['status' => 'returned_failed']));
    }
}
