<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientOrdersFilterTest extends TestCase
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

    public function test_orders_filtering_by_combined_statuses(): void
    {
        // 1. Create orders with different statuses
        $orderPending = Order::create(['client_profile_id' => $this->clientProfile->id, 'status' => 'pending']);
        $orderAssigned = Order::create(['client_profile_id' => $this->clientProfile->id, 'status' => 'assigned']);
        $orderPickedUp = Order::create(['client_profile_id' => $this->clientProfile->id, 'status' => 'picked_up']);
        $orderDelivered = Order::create(['client_profile_id' => $this->clientProfile->id, 'status' => 'delivered']);
        $orderReturned = Order::create(['client_profile_id' => $this->clientProfile->id, 'status' => 'returned']);
        $orderRejected = Order::create(['client_profile_id' => $this->clientProfile->id, 'status' => 'rejected']);
        $orderCancelled = Order::create(['client_profile_id' => $this->clientProfile->id, 'status' => 'cancelled']);

        // 2. Filter by in_transit (should get assigned and picked_up)
        $responseTransit = $this->actingAs($this->clientUser)->get(route('client.orders.index', ['status' => 'in_transit']));
        $responseTransit->assertStatus(200);
        $responseTransitOrders = $responseTransit->viewData('orders');
        $this->assertTrue($responseTransitOrders->contains('id', $orderAssigned->id));
        $this->assertTrue($responseTransitOrders->contains('id', $orderPickedUp->id));
        $this->assertFalse($responseTransitOrders->contains('id', $orderPending->id));
        $this->assertFalse($responseTransitOrders->contains('id', $orderDelivered->id));

        // 3. Filter by returned_failed (should get returned and rejected)
        $responseReturnedFailed = $this->actingAs($this->clientUser)->get(route('client.orders.index', ['status' => 'returned_failed']));
        $responseReturnedFailed->assertStatus(200);
        $responseReturnedFailedOrders = $responseReturnedFailed->viewData('orders');
        $this->assertTrue($responseReturnedFailedOrders->contains('id', $orderReturned->id));
        $this->assertTrue($responseReturnedFailedOrders->contains('id', $orderRejected->id));
        $this->assertFalse($responseReturnedFailedOrders->contains('id', $orderCancelled->id));

        // 4. Assert view contains combined filter dropdown options and does not contain assigned option
        $responseIndex = $this->actingAs($this->clientUser)->get(route('client.orders.index'));
        $responseIndex->assertStatus(200);
        
        $responseIndex->assertSee('value="in_transit"', false);
        $responseIndex->assertSee('value="returned_failed"', false);
        $responseIndex->assertDontSee('value="assigned"', false);
    }
}
