<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientHomeApiTest extends TestCase
{
    use RefreshDatabase;

    private User $clientUser;
    private ClientProfile $clientProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $area = Area::create(['name' => 'Abdali', 'city_id' => $city->id]);

        $this->clientUser = User::factory()->create([
            'role' => 'client_master',
            'status' => 'active',
        ]);

        $this->clientProfile = ClientProfile::create([
            'master_user_id' => $this->clientUser->id,
            'company_name' => 'Test Client Company',
            'city_id' => $city->id,
            'area_id' => $area->id,
            'status' => 'active',
        ]);
    }

    private function makeOrder(array $attributes, ?array $payment = null): Order
    {
        $order = Order::create(array_merge(['client_profile_id' => $this->clientProfile->id], $attributes));

        if (isset($attributes['created_at'])) {
            $order->forceFill(['created_at' => $attributes['created_at']])->saveQuietly();
        }

        if ($payment) {
            $order->payment()->create(array_merge([
                'payment_type' => 'cod',
                'delivery_on_customer' => false,
                'customer_delivery_amount' => 0.00,
                'client_delivery_amount' => 0.00,
            ], $payment));
        }

        return $order;
    }

    public function test_client_home_returns_dashboard_metrics(): void
    {
        // Pending cash: 50 + 5 = 55
        $this->makeOrder(['status' => 'picked_up', 'payment_status' => 'pending'], [
            'order_amount' => 50.00, 'delivery_on_customer' => true, 'customer_delivery_amount' => 5.00,
        ]);
        $this->makeOrder(['status' => 'pending', 'payment_status' => 'pending']);
        $this->makeOrder(['status' => 'assigned', 'payment_status' => 'pending']);

        // Account balance: 100 + 10 = 110, delivered today
        $this->makeOrder(['status' => 'delivered', 'payment_status' => 'with_driver', 'delivered_at' => now()], [
            'order_amount' => 100.00, 'delivery_on_customer' => true, 'customer_delivery_amount' => 10.00,
        ]);
        // Delivered yesterday and already paid out: excluded from balance and delivered_today
        $this->makeOrder(['status' => 'delivered', 'payment_status' => 'paid', 'delivered_at' => now()->subDay(), 'created_at' => now()->subDays(2)], [
            'order_amount' => 150.00,
        ]);

        $this->makeOrder(['status' => 'returned', 'payment_status' => 'pending', 'created_at' => now()->subDays(6)]);
        $this->makeOrder(['status' => 'rejected', 'payment_status' => 'pending', 'created_at' => now()->subDays(6)]);
        // Outside the 7-day window
        $this->makeOrder(['status' => 'cancelled', 'payment_status' => 'pending', 'created_at' => now()->subDays(7)]);

        $response = $this->actingAs($this->clientUser)->getJson(route('api.home'));

        $response->assertOk()
            ->assertJsonPath('data.dashboard.pending_cash', 55.0)
            ->assertJsonPath('data.dashboard.account_balance', 110.0)
            ->assertJsonPath('data.dashboard.pending_pickup', 1)
            ->assertJsonPath('data.dashboard.in_transit', 2)
            ->assertJsonPath('data.dashboard.delivered_today', 1)
            ->assertJsonPath('data.dashboard.returned_failed', 2)
            ->assertJsonPath('data.dashboard.shipping_volume.total', 7)
            ->assertJsonCount(7, 'data.dashboard.shipping_volume.last_7_days');

        $days = $response->json('data.dashboard.shipping_volume.last_7_days');
        $this->assertSame(['date' => now()->subDays(6)->toDateString(), 'count' => 2], $days[0]);
        $this->assertSame(['date' => now()->subDays(2)->toDateString(), 'count' => 1], $days[4]);
        $this->assertSame(['date' => now()->subDay()->toDateString(), 'count' => 0], $days[5]);
        $this->assertSame(['date' => now()->toDateString(), 'count' => 4], $days[6]);

        // Existing fields remain unchanged
        $response->assertJsonPath('data.summary.in_transit', 1);
    }
}
