<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\City;
use App\Models\Area;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\SystemNotification;
use App\Models\FinancialLedgerEntry;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpandedFeaturesTest extends TestCase
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

        // Seed basic users
        $this->admin = User::factory()->create(['role' => 'superadmin']);
        $clientUser = User::factory()->create(['role' => 'client_master']);
        $this->driver = User::factory()->create(['role' => 'driver', 'name' => 'Test Driver']);
        
        DriverProfile::create([
            'user_id' => $this->driver->id,
            'national_id' => '1111111111',
            'license_number' => 'L-1111',
            'license_expiry_date' => now()->addYear(),
        ]);

        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        $this->client = ClientProfile::create([
            'master_user_id' => $clientUser->id,
            'company_name' => 'Reconciliation Merchant',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);
    }

    public function test_invoice_creation_upon_client_payout()
    {
        $this->actingAs($this->admin);

        // Create an order
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 120.00,
            'receiver_name' => 'Customer Recipient',
            'receiver_phone' => '0790000010',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => 'Abdali St Amman',
        ], $this->admin);

        // Mark Delivered (sets status to delivered, logs cod_collection to driver, logs shipping charge)
        $this->orderService->updateStatus($order, 'delivered', [], $this->admin);

        // Settle Cash from Driver to Company
        $this->orderService->settleDriverCash($this->driver, [$order->id], $this->admin);

        // Verify Invoice is NOT created yet
        $this->assertEquals(0, Invoice::count());

        // Process client payout (Company -> Client)
        $payoutCount = $this->orderService->payoutClient($this->client, [$order->id], $this->admin, 'REF-998877', 'Paid COD goods');

        $this->assertEquals(1, $payoutCount);

        // Verify Invoice is auto-generated
        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        $this->assertEquals($this->client->id, $invoice->client_profile_id);
        $this->assertEquals(1, $invoice->total_orders);
        $this->assertEquals(120.00, $invoice->cod_amount);
        $this->assertEquals(10.00, $invoice->shipping_amount);
        $this->assertEquals(110.00, $invoice->net_amount);
        $this->assertEquals('paid', $invoice->status);

        // Fetch invoice route
        $response = $this->get(route('admin.financials.invoices.show', $invoice));
        $response->assertStatus(200);
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('Billed To');
    }

    public function test_support_ticket_creation_and_live_polling()
    {
        // 1. Create ticket
        $ticket = SupportTicket::create([
            'title'    => 'Delayed Delivery Inquiry',
            'category' => 'delivery_issue',
            'priority' => 'high',
        ]);

        $this->assertNotNull($ticket->token);
        $this->assertNotNull($ticket->ticket_number);

        // 2. Post a message to public endpoint
        $response1 = $this->postJson(route('public.support.send', $ticket->token), [
            'message' => 'Where is my delivery?',
        ]);
        $response1->assertStatus(200);
        $response1->assertJson(['success' => true]);

        $this->assertDatabaseHas('support_messages', [
            'support_ticket_id' => $ticket->id,
            'message' => 'Where is my delivery?',
            'sender_name' => 'Customer / Recipient'
        ]);

        // 3. Post reply from admin
        $this->actingAs($this->admin);
        $response2 = $this->postJson(route('admin.support.send', $ticket), [
            'message' => 'We are checking with the driver Ahmed now.',
        ]);
        $response2->assertStatus(200);
        $response2->assertJson(['success' => true]);

        // 4. Poll messages from public client view
        $response3 = $this->get(route('public.support.messages', $ticket->token));
        $response3->assertStatus(200);
        $response3->assertJsonCount(2, 'messages');

        // 5. Resolve Ticket
        $response4 = $this->post(route('admin.support.resolve', $ticket));
        $response4->assertStatus(302); // Redirect back
        $this->assertEquals('resolved', $ticket->fresh()->status);
    }

    public function test_notification_broadcast_and_nav_bell()
    {
        $this->actingAs($this->admin);

        // Verify start with 0
        $respCount = $this->get(route('admin.notifications.unread'));
        $respCount->assertJson(['count' => 0]);

        // 1. Create Broadcast notification
        $response1 = $this->post(route('admin.notifications.store'), [
            'title'   => 'Urgent Maintenance',
            'message' => 'System will be offline for 10 minutes at midnight.',
            'target'  => 'all',
            'type'    => 'warning',
            'link'    => '/admin/dashboard'
        ]);
        $response1->assertStatus(302); // redirects back

        $this->assertDatabaseHas('system_notifications', [
            'title' => 'Urgent Maintenance',
            'role' => 'all',
            'type' => 'warning'
        ]);

        // 2. Fetch via unread bell JSON endpoint
        $respCount2 = $this->get(route('admin.notifications.unread'));
        $respCount2->assertStatus(200);
        $respCount2->assertJson(['count' => 1]);
        $respCount2->assertJsonFragment(['title' => 'Urgent Maintenance']);

        // 3. Mark all read
        $response3 = $this->post(route('admin.notifications.clear'));
        $response3->assertStatus(200);

        // 4. Assert bell is cleared
        $respCount3 = $this->get(route('admin.notifications.unread'));
        $respCount3->assertJson(['count' => 0]);
    }

    public function test_reports_dashboards_and_csv_exports()
    {
        $this->actingAs($this->admin);

        // 1. View Reports Center
        $response1 = $this->get(route('admin.reports.index'));
        $response1->assertStatus(200);
        $response1->assertSee('Reporting Center');

        // 2. View KPIs Dashboard
        $response2 = $this->get(route('admin.reports.kpis'));
        $response2->assertStatus(200);
        $response2->assertSee('KPI Performance Metrics');

        // 3. Export Orders CSV
        $response3 = $this->get(route('admin.reports.export', 'orders'));
        $response3->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response3->headers->get('Content-Type'));
        
        ob_start();
        $response3->sendContent();
        $content = ob_get_clean();
        
        $this->assertStringContainsString('Order Number', $content);
        $this->assertStringContainsString('Client Company', $content);
    }
}
