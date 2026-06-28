<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\City;
use App\Models\Area;
use App\Models\Order;
use App\Services\OrderService;
use App\Enums\DeliveryShift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryShiftTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $clientUser;
    protected $client;
    protected $city;
    protected $area;
    protected $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(\App\Services\SupportNotificationService::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        });

        $this->orderService = app(OrderService::class);

        $this->admin = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
            'phone' => '079' . rand(1000000, 9999999),
        ]);
        $this->clientUser = User::factory()->create([
            'role' => 'client_master',
            'status' => 'active',
            'phone' => '079' . rand(1000000, 9999999),
        ]);
        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        $this->client = ClientProfile::create([
            'master_user_id' => $this->clientUser->id,
            'company_name' => 'Test Merchant',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);
    }

    public function test_can_create_order_with_delivery_shift()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver One',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
            'delivery_shift' => 'before_12pm',
        ], $this->admin);

        $this->assertEquals(DeliveryShift::Before12pm, $order->delivery_shift);
    }

    public function test_can_create_order_with_default_delivery_shift()
    {
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

        $this->assertEquals(DeliveryShift::DoesntMatter, $order->delivery_shift);
    }

    public function test_can_create_order_via_admin_controller()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.orders.store'), [
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver One',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
            'delivery_shift' => 'after_12pm',
        ]);

        $order = Order::latest('id')->first();
        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertEquals(DeliveryShift::After12pm, $order->delivery_shift);
    }

    public function test_can_create_and_edit_order_via_client_controller()
    {
        $response = $this->actingAs($this->clientUser)->post(route('client.orders.store'), [
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver One',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
            'delivery_shift' => 'before_12pm',
        ]);

        $order = Order::latest('id')->first();
        $response->assertRedirect(route('client.orders.show', $order));
        $this->assertEquals(DeliveryShift::Before12pm, $order->delivery_shift);

        // Edit
        $response2 = $this->actingAs($this->clientUser)->patch(route('client.orders.update', $order), [
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver One Edited',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
            'delivery_shift' => 'after_12pm',
        ]);

        $order->refresh();
        $response2->assertRedirect(route('client.orders.show', $order));
        $this->assertEquals(DeliveryShift::After12pm, $order->delivery_shift);
    }

    public function test_client_orders_validation_rejects_invalid_shift()
    {
        $response = $this->actingAs($this->clientUser)->post(route('client.orders.store'), [
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver One',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
            'delivery_shift' => 'invalid_shift_name',
        ]);

        $response->assertSessionHasErrors('delivery_shift');
    }

    public function test_admin_can_edit_order_in_all_statuses()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Original',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
            'delivery_shift' => 'before_12pm',
        ], $this->admin);

        $order->update(['status' => 'delivered']);

        $response = $this->actingAs($this->admin)->patch(route('admin.orders.update', $order), [
            'client_profile_id' => $this->client->id,
            'order_description' => 'Updated contents by admin',
            'payment_type' => 'cod',
            'order_price' => 250.00,
            'receiver_name' => 'Receiver Edited By Admin',
            'receiver_phone' => '0799999999',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '999 New Admin St',
            'delivery_shift' => 'after_12pm',
        ]);

        $order->refresh();
        $response->assertRedirect(route('admin.orders.show', $order));

        $this->assertEquals('delivered', $order->status);
        $this->assertEquals('Updated contents by admin', $order->order_description);
        $this->assertEquals('Receiver Edited By Admin', $order->receiver->receiver_name);
        $this->assertEquals(250.00, $order->payment->order_amount);
        $this->assertEquals(DeliveryShift::After12pm, $order->delivery_shift);
    }

    public function test_admin_notifies_driver_on_single_order_assignment()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Original',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $driverUser = User::factory()->create([
            'role' => 'driver',
            'status' => 'active',
            'phone' => '079' . rand(1000000, 9999999),
        ]);
        \App\Models\DriverProfile::create([
            'user_id' => $driverUser->id,
            'national_id' => '12345' . rand(10000, 99999),
            'license_number' => 'L-' . rand(1000, 9999),
            'license_expiry_date' => now()->addYear(),
        ]);

        $mockNotification = $this->mock(\App\Services\SupportNotificationService::class);
        $mockNotification->shouldReceive('notifyOrdersAssigned')
            ->once()
            ->with($driverUser->id, [$order->id], $this->admin->id);

        $response = $this->actingAs($this->admin)->patch(route('admin.orders.update', $order), [
            'status' => 'picked_up',
            'driver_id' => $driverUser->id,
        ]);

        $response->assertRedirect();
    }

    public function test_admin_sends_reply_notification_on_ticket_creation()
    {
        // Mock SupportNotificationService
        $mockNotification = $this->mock(\App\Services\SupportNotificationService::class);
        $mockNotification->shouldReceive('notifyAdminReply')
            ->once()
            ->with(\Mockery::type(\App\Models\SupportTicket::class), $this->admin->id);

        $response = $this->actingAs($this->admin)->post(route('admin.support.store'), [
            'user_id' => $this->clientUser->id,
            'title' => 'Test Ticket Title',
            'message' => 'Test initial message',
        ]);

        $response->assertRedirect();
    }

    public function test_client_notified_when_order_delivered()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Original',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $mockNotification = $this->mock(\App\Services\SupportNotificationService::class);
        $mockNotification->shouldReceive('notifyClientOrderStatusChanged')
            ->once()
            ->with(\Mockery::type(\App\Models\Order::class), 'delivered', $this->admin->id);

        $this->orderService->updateStatus($order, 'delivered', [], $this->admin);
    }

    public function test_client_notified_when_order_rejected()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Original',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ], $this->admin);

        $mockNotification = $this->mock(\App\Services\SupportNotificationService::class);
        $mockNotification->shouldReceive('notifyClientOrderStatusChanged')
            ->once()
            ->with(\Mockery::type(\App\Models\Order::class), 'rejected', $this->admin->id);

        $this->orderService->updateStatus($order, 'rejected', [], $this->admin);
    }

    public function test_client_import_validation_allows_review_with_errors()
    {
        $csvContent = "order_description,payment_type,delivery_on_customer,delivery_customer_amount,order_price,receiver_name,receiver_phone,city_id,area_id,address_text,notes,delivery_shift\n" .
                      "Test contents,invalid_payment,false,0.00,10.00,Receiver,0790000000,999,999,Address,notes,before_12pm";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('orders.csv', $csvContent);

        $response = $this->actingAs($this->clientUser)->post(route('client.orders.import.submit'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('client.orders.import.review'));

        $response2 = $this->actingAs($this->clientUser)->get(route('client.orders.import.review'));
        $response2->assertStatus(200);
        $response2->assertSessionHas('client_import_pending_rows');
        $response2->assertSessionHas('client_import_errors');
    }

    public function test_admin_import_validation_allows_review_with_errors()
    {
        $csvContent = "client_id,order_description,payment_type,delivery_on_customer,delivery_customer_amount,order_price,receiver_name,receiver_phone,city_id,area_id,address_text,notes,delivery_shift\n" .
                      "999,Test contents,invalid_payment,false,0.00,10.00,Receiver,0790000000,999,999,Address,notes,before_12pm";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('orders.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('admin.orders.import.upload'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('admin.orders.import.review'));

        $response2 = $this->actingAs($this->admin)->get(route('admin.orders.import.review'));
        $response2->assertStatus(200);
        $response2->assertSessionHas('import_pending_rows');
        $response2->assertSessionHas('import_errors');
    }

    public function test_client_can_export_orders_csv()
    {
        $response = $this->actingAs($this->clientUser)->get(route('client.orders.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_export_orders_csv()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.orders.export'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_client_can_print_single_order()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Print Client',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Client St',
        ], $this->admin);

        $response = $this->actingAs($this->clientUser)->get(route('client.orders.print', $order));
        $response->assertStatus(200);
        $response->assertSee("SA'EE LOGISTICS", false);
    }

    public function test_admin_can_print_single_order()
    {
        $order = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Print Admin',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Admin St',
        ], $this->admin);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.print', $order));
        $response->assertStatus(200);
        $response->assertSee("SA'EE LOGISTICS", false);
    }

    public function test_client_can_bulk_print_orders()
    {
        $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Bulk Client',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Client Bulk St',
        ], $this->admin);

        $response = $this->actingAs($this->clientUser)->get(route('client.orders.print-all'));
        $response->assertStatus(200);
        $response->assertSee("SA'EE LOGISTICS", false);
    }

    public function test_admin_can_bulk_print_orders()
    {
        $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Receiver Bulk Admin',
            'receiver_phone' => '0790000001',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Admin Bulk St',
        ], $this->admin);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.print-all'));
        $response->assertStatus(200);
        $response->assertSee("SA'EE LOGISTICS", false);
    }
}
