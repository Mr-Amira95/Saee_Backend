<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\City;
use App\Models\Area;
use App\Models\Order;
use App\Models\WhatsAppTemplate;
use App\Models\WhatsAppLog;
use App\Models\SystemNotification;
use App\Services\OrderService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppNotificationTest extends TestCase
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

        $this->driver = User::factory()->create(['role' => 'driver', 'name' => 'Ahmed Driver', 'phone' => '0799999999']);
        DriverProfile::create([
            'user_id' => $this->driver->id,
            'national_id' => '1234567890',
            'license_number' => 'L-1234',
            'license_expiry_date' => now()->addYear(),
        ]);

        // Seed default templates
        WhatsAppTemplate::create([
            'event' => 'order_created',
            'template_body' => 'Hello {customer_name}, order #{order_number} is created. Driver: {driver_name} ({driver_phone}). Link: {location_link}',
        ]);
        WhatsAppTemplate::create([
            'event' => 'order_delivered',
            'template_body' => 'Hello {customer_name}, order #{order_number} is delivered. Review: {location_link}',
        ]);
        WhatsAppTemplate::create([
            'event' => 'order_rejected',
            'template_body' => 'Hello {customer_name}, order #{order_number} is rejected. Reason: {rejection_reason}. Link: {location_link}',
        ]);
    }

    public function test_whatsapp_service_compiles_template_placeholders()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'John Doe',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $service = app(WhatsAppService::class);
        $compiled = $service->compileMessage($order, 'Name: {customer_name}, Num: {order_number}, Driver: {driver_name}', 'order_created');

        $this->assertStringContainsString('Name: John Doe', $compiled);
        $this->assertStringContainsString('Num: ' . $order->order_number, $compiled);
        $this->assertStringContainsString('Driver: Ahmed Driver', $compiled);
    }

    public function test_order_creation_triggers_whatsapp_notification()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'John Doe',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $this->assertDatabaseHas('whatsapp_logs', [
            'order_id' => $order->id,
            'phone' => '0790000001',
            'status' => 'simulated',
        ]);

        $log = WhatsAppLog::where('order_id', $order->id)->first();
        $this->assertStringContainsString('Hello John Doe', $log->message);
        $this->assertStringContainsString('order #' . $order->order_number, $log->message);
        $this->assertStringContainsString('Driver: Ahmed Driver', $log->message);
    }

    public function test_order_delivery_triggers_portal_notification_and_no_whatsapp()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'John Doe',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        // Transition status to delivered
        $this->orderService->updateStatus($order, 'delivered', [], $this->admin);

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->client->master_user_id,
            'entity_type' => 'single_order',
            'entity_id' => $order->id,
            'title' => 'Order Delivered',
        ]);

        $deliveredWhatsapp = WhatsAppLog::where('order_id', $order->id)
            ->where('message', 'like', '%delivered%')
            ->first();

        $this->assertNull($deliveredWhatsapp);
    }

    public function test_order_rejection_triggers_portal_notification_and_no_whatsapp()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'John Doe',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        // Transition status to rejected
        $this->orderService->updateStatus($order, 'rejected', [
            'notes' => 'Customer is on vacation'
        ], $this->admin);

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->client->master_user_id,
            'entity_type' => 'single_order',
            'entity_id' => $order->id,
            'title' => 'Order Rejected',
        ]);

        $rejectedWhatsapp = WhatsAppLog::where('order_id', $order->id)
            ->where('message', 'like', '%rejected%')
            ->first();

        $this->assertNull($rejectedWhatsapp);
    }

    public function test_order_picked_up_triggers_portal_notification_and_no_whatsapp()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'John Doe',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        // Transition status to picked_up
        $this->orderService->updateStatus($order, 'picked_up', [], $this->admin);

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $this->client->master_user_id,
            'entity_type' => 'single_order',
            'entity_id' => $order->id,
            'title' => 'Order Picked Up',
        ]);

        $pickedUpWhatsapp = WhatsAppLog::where('order_id', $order->id)
            ->where('message', 'like', '%picked%')
            ->first();

        $this->assertNull($pickedUpWhatsapp);
    }

    public function test_public_customer_location_sharing()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'John Doe',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        // GET public share-location view
        $response = $this->get(route('public.share-location', $order->order_number));
        $response->assertStatus(200);
        $response->assertSee('Delivery Location Sharing');
        $response->assertSee($order->order_number);

        // POST coordinates submission
        $postResponse = $this->post(route('public.share-location.update', $order->order_number), [
            'latitude' => 31.9566,
            'longitude' => 35.9114,
        ]);

        $postResponse->assertStatus(200);
        $postResponse->assertJson(['success' => true]);

        $this->assertEquals('31.9566,35.9114', $order->fresh()->address_location);

        $this->assertDatabaseHas('order_tracking_logs', [
            'order_id' => $order->id,
            'latitude' => 31.9566,
            'longitude' => 35.9114,
        ]);
    }
}
