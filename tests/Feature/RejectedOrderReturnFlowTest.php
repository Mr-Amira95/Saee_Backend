<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Attendance;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\HandoverRequest;
use App\Models\Order;
use App\Models\RejectionReason;
use App\Models\User;
use App\Services\ClientStatementService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * rejected → in_office (handover approved) → out_for_return (return driver
 * assigned) → returned (client confirms), and who pays the delivery fee of a
 * rejected order (client option "rejected orders are payable").
 */
class RejectedOrderReturnFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $clientUser;
    private ClientProfile $client;
    private User $driver;
    private User $returnDriver;
    private City $city;
    private Area $area;
    private RejectionReason $reason;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderService::class);

        $this->admin = User::factory()->create(['role' => 'superadmin', 'status' => 'active']);

        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        $this->clientUser = User::factory()->create(['role' => 'client_master', 'status' => 'active']);
        $this->client = ClientProfile::create([
            'master_user_id' => $this->clientUser->id,
            'company_name'   => 'Test Merchant',
            'city_id'        => $this->city->id,
            'area_id'        => $this->area->id,
            'status'         => 'active',
        ]);

        $this->driver = $this->makeDriver('1111111111');
        $this->returnDriver = $this->makeDriver('2222222222');

        $this->reason = RejectionReason::create(['reason' => 'Customer refused', 'is_active' => true]);
    }

    public function test_rejected_order_goes_to_the_office_then_back_to_the_client_who_confirms_it(): void
    {
        $order = $this->pickedUpOrder();

        $this->actingAs($this->driver, 'sanctum')
            ->postJson("/api/orders/{$order->id}/reject", ['rejection_reason_id' => $this->reason->id])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');

        // The delivery driver can no longer mark a rejected order as returned himself
        $this->actingAs($this->driver, 'sanctum')
            ->postJson("/api/orders/{$order->id}/return")
            ->assertStatus(403);

        // Handover: the office receives the order — it is not returned yet
        $this->actingAs($this->driver, 'sanctum')
            ->postJson('/api/driver/confirm-handover', ['notes' => 'One return.'])
            ->assertOk()
            ->assertJsonPath('data.returned_count', 1);

        $handover = HandoverRequest::where('driver_id', $this->driver->id)->firstOrFail();
        $this->actingAs($this->admin)
            ->post("/admin/financials/handover-requests/{$handover->id}/approve")
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('in_office', $order->status);
        $this->assertNull($order->returned_at);
        $this->assertDatabaseMissing('financial_ledger_entries', ['order_id' => $order->id, 'type' => 'shipping_charge']);

        // Not on the client statement while it is still at the office
        $this->assertSame(0, app(ClientStatementService::class)->ordersQuery(['client_id' => $this->client->id])->count());

        // Admin assigns another driver to take it back to the client
        $this->actingAs($this->admin)
            ->post('/admin/orders/assign-driver', ['order_ids' => [$order->id], 'driver_id' => $this->returnDriver->id])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('out_for_return', $order->status);
        $this->assertSame($this->returnDriver->driverProfile->id, $order->return_driver_profile_id);
        $this->assertSame($this->driver->driverProfile->id, $order->driver_profile_id, 'The delivery driver is kept.');
        $this->assertDatabaseHas('system_notifications', ['user_id' => $this->returnDriver->id, 'title' => 'Order to Return']);

        // The return driver sees it as a return task
        $this->actingAs($this->returnDriver, 'sanctum')
            ->getJson('/api/orders?return_tasks=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $order->id)
            ->assertJsonPath('data.0.is_return_task', true);

        $this->actingAs($this->driver, 'sanctum')
            ->getJson('/api/orders?return_tasks=1')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        // Only the return driver can hand it back
        $this->actingAs($this->driver, 'sanctum')
            ->postJson("/api/orders/{$order->id}/return")
            ->assertStatus(403);

        $this->checkIn($this->returnDriver);
        $this->actingAs($this->returnDriver, 'sanctum')
            ->postJson("/api/orders/{$order->id}/return")
            ->assertOk()
            ->assertJsonPath('data.status', 'returned')
            ->assertJsonPath('data.awaiting_return_confirmation', true);

        $order->refresh();
        $this->assertSame('returned', $order->status);
        $this->assertNotNull($order->returned_at);
        $this->assertNull($order->return_confirmed_at);

        // The client is asked to confirm
        $this->assertDatabaseHas('system_notifications', [
            'user_id'     => $this->clientUser->id,
            'title'       => 'Order Returned',
            'entity_type' => 'single_order',
            'entity_id'   => $order->id,
        ]);

        $this->actingAs($this->clientUser, 'sanctum')
            ->getJson('/api/orders?awaiting_return_confirmation=1')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($this->clientUser, 'sanctum')
            ->postJson("/api/orders/{$order->id}/confirm-return")
            ->assertOk()
            ->assertJsonPath('data.awaiting_return_confirmation', false);

        $order->refresh();
        $this->assertNotNull($order->return_confirmed_at);
        $this->assertSame($this->clientUser->id, $order->return_confirmed_by);

        // Confirming twice is refused
        $this->actingAs($this->clientUser, 'sanctum')
            ->postJson("/api/orders/{$order->id}/confirm-return")
            ->assertStatus(422);
    }

    public function test_client_confirms_a_returned_order_from_the_portal(): void
    {
        $order = $this->pickedUpOrder();
        $this->orderService->updateStatus($order, 'rejected', [], $this->driver);
        $this->orderService->updateStatus($order, 'in_office', [], $this->admin);
        $this->orderService->assignReturnDriver($order, $this->returnDriver, $this->admin);
        $this->orderService->updateStatus($order, 'returned', [], $this->returnDriver);

        $this->actingAs($this->clientUser)
            ->get(route('client.orders.show', $order))
            ->assertOk()
            ->assertSee(route('client.orders.confirm-return', $order));

        $this->actingAs($this->clientUser)
            ->post(route('client.orders.confirm-return', $order))
            ->assertRedirect();

        $this->assertNotNull($order->fresh()->return_confirmed_at);

        // Another client cannot confirm it
        $otherUser = User::factory()->create(['role' => 'client_master', 'status' => 'active']);
        ClientProfile::create(['master_user_id' => $otherUser->id, 'company_name' => 'Other', 'status' => 'active']);

        $this->actingAs($otherUser)
            ->post(route('client.orders.confirm-return', $order))
            ->assertStatus(403);
    }

    public function test_rejected_orders_are_free_when_the_client_option_is_off(): void
    {
        $this->client->update(['charge_rejected_orders' => false]);

        $order = $this->pickedUpOrder(['delivery_on_customer' => true, 'delivery_customer_amount' => 3.00]);

        // Even if the app sends "collected", nothing is payable for this client
        $this->actingAs($this->driver, 'sanctum')
            ->postJson("/api/orders/{$order->id}/reject", [
                'rejection_reason_id'    => $this->reason->id,
                'delivery_fee_collected' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.rejection_fee_status', Order::REJECTION_FEE_NOT_CHARGED)
            ->assertJsonPath('data.ask_delivery_fee_on_reject', false);

        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertDatabaseMissing('financial_ledger_entries', ['order_id' => $order->id]);

        $this->returnToClient($order);

        $this->assertDatabaseMissing('financial_ledger_entries', ['order_id' => $order->id, 'type' => 'shipping_charge']);

        $statements = app(ClientStatementService::class);
        $this->assertSame(0.0, $statements->amounts($order->fresh()->load('payment'))['client_delivery']);
        $this->assertSame(0.0, $statements->totals($statements->ordersQuery(['client_id' => $this->client->id]))['client_delivery']);
    }

    public function test_delivery_fee_collected_from_the_customer_covers_the_fee(): void
    {
        Storage::fake('public');

        $order = $this->pickedUpOrder(['delivery_on_customer' => true, 'delivery_customer_amount' => 3.00]);

        $this->actingAs($this->driver, 'sanctum')
            ->getJson("/api/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.ask_delivery_fee_on_reject', true);

        $this->actingAs($this->driver, 'sanctum')
            ->postJson("/api/orders/{$order->id}/reject", [
                'rejection_reason_id'    => $this->reason->id,
                'delivery_fee_collected' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.rejection_fee_status', Order::REJECTION_FEE_COLLECTED);

        // The driver holds the delivery fee only — never the COD amount
        $this->assertSame('with_driver', $order->fresh()->payment_status);
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id'     => $order->id,
            'from_account' => 'customer',
            'to_account'   => 'driver',
            'amount'       => 3.00,
            'type'         => 'delivery_collection',
        ]);

        $this->actingAs($this->driver, 'sanctum')
            ->getJson('/api/home')
            ->assertOk()
            ->assertJsonPath('data.summary.cash_collected.total', fn ($total) => (float) $total === 3.0);

        // Holding cash → the handover needs a payment method and proof
        $this->actingAs($this->driver, 'sanctum')
            ->postJson('/api/driver/confirm-handover', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['payment_method', 'proof_image']);

        $this->actingAs($this->driver, 'sanctum')
            ->postJson('/api/driver/confirm-handover', [
                'payment_method' => 'cash',
                'proof_image'    => UploadedFile::fake()->image('proof.jpg'),
            ])
            ->assertOk();

        $handover = HandoverRequest::where('driver_id', $this->driver->id)->firstOrFail();
        $this->orderService->approveHandover($handover, $this->admin);

        $order->refresh();
        $this->assertSame('in_office', $order->status);
        $this->assertSame('with_company', $order->payment_status);
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id'     => $order->id,
            'from_account' => 'driver',
            'to_account'   => 'company',
            'amount'       => 3.00,
            'type'         => 'driver_settlement',
        ]);

        $this->orderService->assignReturnDriver($order, $this->returnDriver, $this->admin);
        $this->orderService->updateStatus($order, 'returned', [], $this->returnDriver);

        // The client is charged nothing: the collected cash covers Saee's fee
        $this->assertDatabaseMissing('financial_ledger_entries', ['order_id' => $order->id, 'type' => 'shipping_charge']);

        $statements = app(ClientStatementService::class);
        $amounts = $statements->amounts($order->fresh()->load('payment'));
        $this->assertSame(0.0, $amounts['client_delivery']);
        $this->assertSame(0.0, $amounts['net']);
    }

    public function test_client_is_charged_when_the_customer_did_not_pay_the_delivery_fee(): void
    {
        $order = $this->pickedUpOrder(['delivery_on_customer' => true, 'delivery_customer_amount' => 3.00]);

        // The current app does not send the field yet: treated as "not collected"
        $this->actingAs($this->driver, 'sanctum')
            ->postJson("/api/orders/{$order->id}/reject", ['rejection_reason_id' => $this->reason->id])
            ->assertOk()
            ->assertJsonPath('data.rejection_fee_status', Order::REJECTION_FEE_CLIENT_CHARGED);

        $this->assertSame('pending', $order->fresh()->payment_status);
        $this->assertDatabaseMissing('financial_ledger_entries', ['order_id' => $order->id]);

        $this->returnToClient($order);

        // Charged once the order is back with the client
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id'     => $order->id,
            'from_account' => 'client',
            'to_account'   => 'company',
            'amount'       => 10.00,
            'type'         => 'shipping_charge',
        ]);

        $statements = app(ClientStatementService::class);
        $totals = $statements->totals($statements->ordersQuery(['client_id' => $this->client->id]));
        $this->assertSame(1, $totals['orders']);
        $this->assertSame(10.0, $totals['client_delivery']);
        $this->assertSame(-10.0, $totals['net']);
    }

    public function test_admin_sets_the_rejected_orders_option_on_the_client(): void
    {
        $this->assertTrue($this->client->fresh()->charge_rejected_orders, 'Existing clients keep being charged.');

        $this->actingAs($this->admin)
            ->get(route('admin.clients.edit', $this->client))
            ->assertOk()
            ->assertSee('name="charge_rejected_orders"', false);
    }

    private function makeDriver(string $nationalId): User
    {
        $driver = User::factory()->create(['role' => 'driver', 'status' => 'active']);

        DriverProfile::create([
            'user_id'             => $driver->id,
            'national_id'         => $nationalId,
            'license_number'      => 'L-' . $nationalId,
            'license_expiry_date' => now()->addYear(),
        ]);

        return $driver;
    }

    private function checkIn(User $driver): void
    {
        Attendance::create(['user_id' => $driver->id, 'date' => today(), 'check_in_at' => now()]);
    }

    /** An order picked up by the (checked-in) delivery driver. */
    private function pickedUpOrder(array $data = []): Order
    {
        $order = $this->orderService->createOrder(array_merge([
            'client_profile_id' => $this->client->id,
            'driver_id'         => $this->driver->id,
            'payment_type'      => 'cod',
            'order_price'       => 50.00,
            'receiver_name'     => 'Receiver',
            'receiver_phone'    => '0790000001',
            'city_id'           => $this->city->id,
            'area_id'           => $this->area->id,
            'address_text'      => '1 Abdali St',
        ], $data), $this->admin);

        $this->orderService->updateStatus($order, 'picked_up', [], $this->admin);

        if (! Attendance::where('user_id', $this->driver->id)->exists()) {
            $this->checkIn($this->driver);
        }

        return $order->fresh();
    }

    /** rejected → in_office → out_for_return → returned */
    private function returnToClient(Order $order): void
    {
        $order->refresh();
        $this->orderService->updateStatus($order, 'in_office', [], $this->admin);
        $this->orderService->assignReturnDriver($order, $this->returnDriver, $this->admin);
        $this->orderService->updateStatus($order, 'returned', [], $this->returnDriver);
    }
}
