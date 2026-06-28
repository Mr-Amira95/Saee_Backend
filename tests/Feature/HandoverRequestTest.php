<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\City;
use App\Models\Area;
use App\Models\Order;
use App\Models\HandoverRequest;
use App\Models\FinancialLedgerEntry;
use App\Models\SystemNotification;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandoverRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $client;
    protected $driver;
    protected $city;
    protected $area;
    protected $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);

        // Create core entities
        $this->admin = User::factory()->create(['role' => 'superadmin', 'status' => 'active']);
        
        $clientUser = User::factory()->create(['role' => 'client_master', 'status' => 'active']);
        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);
        
        $this->client = ClientProfile::create([
            'master_user_id' => $clientUser->id,
            'company_name' => 'Test Merchant',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);

        $this->driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);
        DriverProfile::create([
            'user_id' => $this->driver->id,
            'national_id' => '1234567890',
            'license_number' => 'L-1234',
            'license_expiry_date' => now()->addYear(),
        ]);
    }

    public function test_driver_can_submit_handover_request_for_delivered_and_rejected_orders()
    {
        // 1. Create a delivered order (has cash with driver)
        $order1 = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver One',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $this->orderService->updateStatus($order1, 'delivered', [], $this->admin);

        // 2. Create a rejected order
        $order2 = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 50.00,
            'receiver_name' => 'Receiver Two',
            'receiver_phone' => '0790000002',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '456 Abdali St',
        ], $this->admin);

        $this->orderService->updateStatus($order2, 'rejected', [], $this->admin);

        // Verify pre-handover state
        $this->assertEquals('with_driver', $order1->fresh()->payment_status);
        $this->assertEquals('rejected', $order2->fresh()->status);
        $this->assertNull($order1->fresh()->handover_request_id);
        $this->assertNull($order2->fresh()->handover_request_id);

        // 3. Driver confirms handover (checkout) via API
        $response = $this->actingAs($this->driver, 'sanctum')
            ->postJson('/api/driver/confirm-handover', [
                'notes' => 'End of shift cash and returns handover.',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Assert HandoverRequest was created as pending
        $handoverRequest = HandoverRequest::where('driver_id', $this->driver->id)->first();
        $this->assertNotNull($handoverRequest);
        $this->assertEquals('pending', $handoverRequest->status);
        $this->assertEquals('End of shift cash and returns handover.', $handoverRequest->notes);

        // Assert orders now reference the handover request but keep their status/payment_status
        $this->assertEquals($handoverRequest->id, $order1->fresh()->handover_request_id);
        $this->assertEquals($handoverRequest->id, $order2->fresh()->handover_request_id);
        
        $this->assertEquals('with_driver', $order1->fresh()->payment_status);
        $this->assertEquals('rejected', $order2->fresh()->status);

        // Assert notification created for admins
        $this->assertDatabaseHas('system_notifications', [
            'entity_type' => 'handover_request',
            'entity_id' => $handoverRequest->id,
            'title' => 'New Checkout Handover Request',
        ]);

        // 4. Test driver cannot duplicate handover request while one is pending
        $duplicateResponse = $this->actingAs($this->driver, 'sanctum')
            ->postJson('/api/driver/confirm-handover', [
                'notes' => 'Duplicate attempt.',
            ]);

        $duplicateResponse->assertStatus(500); // Throws Exception for already pending request

        // 5. Admin approves handover
        $adminResponse = $this->actingAs($this->admin)
            ->post('/admin/financials/handover-requests/' . $handoverRequest->id . '/approve');

        $adminResponse->assertRedirect();

        // Assert HandoverRequest is approved
        $this->assertEquals('approved', $handoverRequest->fresh()->status);
        $this->assertEquals($this->admin->id, $handoverRequest->fresh()->approved_by);

        // Assert order statuses are updated
        $this->assertEquals('with_company', $order1->fresh()->payment_status);
        $this->assertEquals('returned', $order2->fresh()->status);
        $this->assertEquals('no_payment', $order2->fresh()->payment_status);

        // Assert ledger entries created for driver settlement and shipping charges
        // Driver settlement (from driver to company) for cod_collection amount (100.00 order amount)
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id' => $order1->id,
            'from_account' => 'driver',
            'to_account' => 'company',
            'amount' => 100.00,
            'type' => 'driver_settlement',
        ]);

        // Shipping charge (from client to company) for returned order (10.00 client delivery amount)
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id' => $order2->id,
            'from_account' => 'client',
            'to_account' => 'company',
            'amount' => 10.00,
            'type' => 'shipping_charge',
        ]);
    }
}
