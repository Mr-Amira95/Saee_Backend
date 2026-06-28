<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\City;
use App\Models\Area;
use App\Models\Order;
use App\Models\FinancialLedgerEntry;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFinanceTest extends TestCase
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

        // Seed basic entities
        $this->admin = User::factory()->create(['role' => 'superadmin']);
        
        $clientUser = User::factory()->create(['role' => 'client_master']);
        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);
        
        $this->client = ClientProfile::create([
            'master_user_id' => $clientUser->id,
            'company_name' => 'Test Merchant',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);

        $this->driver = User::factory()->create(['role' => 'driver']);
        DriverProfile::create([
            'user_id' => $this->driver->id,
            'national_id' => '1234567890',
            'license_number' => 'L-1234',
            'license_expiry_date' => now()->addYear(),
        ]);
    }

    public function test_order_creation_calculates_correct_delivery_price()
    {
        // 1. Test default city price
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver One',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $this->assertEquals(10.00, $order->delivery_amount);

        // 2. Test custom client-city price
        $this->client->deliveryPrices()->create([
            'city_id' => $this->city->id,
            'delivery_price' => 7.50
        ]);

        $order2 = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Two',
            'receiver_phone' => '0790000002',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '456 Abdali St',
        ], $this->admin);

        $this->assertEquals(7.50, $order2->delivery_amount);
    }

    public function test_order_delivery_creates_correct_ledger_entries_for_cod()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 150.00,
            'receiver_name' => 'Receiver One',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $this->orderService->updateStatus($order, 'delivered', [], $this->admin);

        // Check order state
        $this->assertEquals('delivered', $order->fresh()->status);
        $this->assertEquals('with_driver', $order->fresh()->payment_status);

        // Assert ledger entries:
        // 1. Shipping charge: client -> company for 10.00
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id' => $order->id,
            'from_account' => 'client',
            'to_account' => 'company',
            'amount' => 10.00,
            'type' => 'shipping_charge'
        ]);

        // 2. COD collection: customer -> driver for 150.00
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id' => $order->id,
            'from_account' => 'customer',
            'to_account' => 'driver',
            'amount' => 150.00,
            'type' => 'cod_collection'
        ]);
    }

    public function test_order_delivery_with_delivery_on_customer()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'prepaid',
            'delivery_on_customer' => true,
            'delivery_customer_amount' => 12.00,
            'receiver_name' => 'Receiver One',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $this->orderService->updateStatus($order, 'delivered', [], $this->admin);

        // Assert shipping charge: client -> company for 10.00
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id' => $order->id,
            'from_account' => 'client',
            'to_account' => 'company',
            'amount' => 10.00,
            'type' => 'shipping_charge'
        ]);

        // Assert delivery collection: customer -> driver for 12.00
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id' => $order->id,
            'from_account' => 'customer',
            'to_account' => 'driver',
            'amount' => 12.00,
            'type' => 'delivery_collection'
        ]);

        // Assert delivery reimbursement: company -> client for 12.00
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id' => $order->id,
            'from_account' => 'company',
            'to_account' => 'client',
            'amount' => 12.00,
            'type' => 'client_payout'
        ]);
    }

    public function test_driver_cash_settlement_moves_cash_to_company()
    {
        $order = $this->orderService->createOrder([
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

        $this->orderService->updateStatus($order, 'delivered', [], $this->admin);

        // Driver holds 100.00 COD
        $this->orderService->settleDriverCash($this->driver, [$order->id], $this->admin, 'REC-001');

        // Assert ledger entry: driver -> company for 100.00
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id' => $order->id,
            'from_account' => 'driver',
            'to_account' => 'company',
            'amount' => 100.00,
            'type' => 'driver_settlement',
            'reference_number' => 'REC-001'
        ]);

        // Since it's COD, company now holds the cash. Order is NOT yet marked fully 'paid' to client
        $this->assertNotEquals('paid', $order->fresh()->payment_status);

        // Now payout the client
        $this->orderService->payoutClient($this->client, [$order->id], $this->admin, 'PAY-991');

        $this->assertEquals('paid', $order->fresh()->payment_status);

        // Assert payout entry: company -> client for 100.00
        $this->assertDatabaseHas('financial_ledger_entries', [
            'order_id' => $order->id,
            'from_account' => 'company',
            'to_account' => 'client',
            'amount' => 100.00,
            'type' => 'client_payout',
            'reference_number' => 'PAY-991'
        ]);
    }

    public function test_client_payout_creates_invoice_with_attachment_and_notifies_client_users()
    {
        $order = $this->orderService->createOrder([
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

        $this->orderService->updateStatus($order, 'delivered', [], $this->admin);
        $this->orderService->settleDriverCash($this->driver, [$order->id], $this->admin, 'REC-001');

        // Verify database does not have payout notifications
        $this->assertDatabaseMissing('system_notifications', [
            'entity_type' => 'client_payout',
        ]);

        // Trigger payout with notes and attachment path
        $this->orderService->payoutClient(
            $this->client,
            [$order->id],
            $this->admin,
            'PAY-TEST-ATTACH',
            'Settle with receipt',
            'payout-attachments/receipt.pdf'
        );

        // Assert Invoice is created with attachment
        $this->assertDatabaseHas('invoices', [
            'client_profile_id' => $this->client->id,
            'attachment_path' => 'payout-attachments/receipt.pdf',
            'status' => 'paid',
        ]);

        // Assert system notification is created for the client master user
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->client->master_user_id,
            'entity_type' => 'client_payout',
            'title' => 'COD Payout Received',
        ]);
    }
}
