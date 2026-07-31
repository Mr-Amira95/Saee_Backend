<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\User;
use App\Models\WhatsAppLog;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppLogsSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversations_group_by_phone_across_orders_and_render_chat_ui()
    {
        $admin = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($admin);

        $orderService = app(OrderService::class);
        $clientUser = User::factory()->create(['role' => 'client_master']);
        $city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $area = Area::create(['name' => 'Abdali', 'city_id' => $city->id]);
        $client = ClientProfile::create([
            'master_user_id' => $clientUser->id,
            'company_name' => 'Test Merchant',
            'city_id' => $city->id,
            'area_id' => $area->id,
            'status' => 'active',
        ]);
        $driver = User::factory()->create(['role' => 'driver', 'phone' => '0799999999']);
        DriverProfile::create([
            'user_id' => $driver->id,
            'national_id' => '1234567890',
            'license_number' => 'L-1234',
            'license_expiry_date' => now()->addYear(),
        ]);

        // Two separate orders for the SAME customer phone, in different raw formats.
        $orderA = $orderService->createOrder([
            'client_profile_id' => $client->id,
            'driver_id' => $driver->id,
            'payment_type' => 'cod',
            'order_price' => 100.00,
            'receiver_name' => 'Jane Repeat-Customer',
            'receiver_phone' => '0790000001',
            'city_id' => $city->id,
            'area_id' => $area->id,
            'address_text' => '123 Abdali St',
        ], $admin);

        $orderB = $orderService->createOrder([
            'client_profile_id' => $client->id,
            'driver_id' => $driver->id,
            'payment_type' => 'cod',
            'order_price' => 50.00,
            'receiver_name' => 'Jane Repeat-Customer',
            'receiver_phone' => '0790000001',
            'city_id' => $city->id,
            'area_id' => $area->id,
            'address_text' => '456 Rainbow St',
        ], $admin);

        // Inbound text reply on order A, in the webhook's international-digits format.
        WhatsAppLog::create([
            'order_id' => $orderA->id,
            'phone' => '962790000001',
            'message' => 'Sure, delivering now',
            'status' => 'received',
            'direction' => 'inbound',
            'message_type' => 'text',
        ]);

        // Inbound location share on order B.
        WhatsAppLog::create([
            'order_id' => $orderB->id,
            'phone' => '962790000001',
            'message' => 'Shared location',
            'status' => 'received',
            'direction' => 'inbound',
            'message_type' => 'location',
            'meta' => ['latitude' => 31.9566, 'longitude' => 35.9114],
        ]);

        $normalized = WhatsAppLog::normalizePhone('0790000001');
        $this->assertEquals('962790000001', $normalized);

        $indexResponse = $this->get(route('admin.whatsapp-logs.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Jane Repeat-Customer');
        $indexResponse->assertSee('+1 more order(s)', false);

        $showResponse = $this->get(route('admin.whatsapp-logs.show', $normalized));
        $showResponse->assertOk();
        $showResponse->assertSee('Jane Repeat-Customer');
        $showResponse->assertSee('Sure, delivering now');
        $showResponse->assertSee('maps.google.com', false);
        $showResponse->assertSee('#' . $orderA->order_number);
        $showResponse->assertSee('#' . $orderB->order_number);
    }
}
