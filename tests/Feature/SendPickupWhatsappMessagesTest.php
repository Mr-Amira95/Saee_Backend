<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsappMessageJob;
use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\OrderReceiver;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendPickupWhatsappMessagesTest extends TestCase
{
    use RefreshDatabase;

    private ClientProfile $clientProfile;
    private City $city;
    private Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        $clientUser = User::factory()->create(['role' => 'client_master', 'status' => 'active']);

        $this->clientProfile = ClientProfile::create([
            'master_user_id' => $clientUser->id,
            'company_name' => 'Test Client Company',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'status' => 'active',
        ]);
    }

    private function makeOrder(string $status, string $phone): Order
    {
        $order = Order::create(['client_profile_id' => $this->clientProfile->id, 'status' => $status, 'order_number' => 'ORD-' . $phone]);

        OrderReceiver::create([
            'order_id' => $order->id,
            'receiver_name' => 'John Doe',
            'receiver_phone' => $phone,
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '123 Abdali St',
        ]);

        return $order;
    }

    public function test_picking_up_an_order_does_not_send_whatsapp_immediately(): void
    {
        Queue::fake();

        $order = $this->makeOrder('assigned', '0790000001');
        $admin = User::factory()->create(['role' => 'superadmin']);

        app(\App\Services\OrderService::class)->updateStatus($order, 'picked_up', [], $admin);

        Queue::assertNotPushed(SendWhatsappMessageJob::class, fn ($job) => $job->event === 'assign_order');
    }

    public function test_command_sends_once_to_picked_up_orders_only(): void
    {
        Queue::fake();

        $pickedUp  = $this->makeOrder('picked_up', '0790000001');
        $delivered = $this->makeOrder('delivered', '0790000002');

        $this->artisan('whatsapp:send-pickup-messages')->assertSuccessful();

        Queue::assertPushed(SendWhatsappMessageJob::class, 1);
        Queue::assertPushed(SendWhatsappMessageJob::class, fn ($job) => $job->event === 'assign_order'
            && $job->orderId === $pickedUp->id
            && $job->phone === '0790000001');

        $this->assertNotNull($pickedUp->fresh()->pickup_whatsapp_sent_at);
        $this->assertNull($delivered->fresh()->pickup_whatsapp_sent_at);

        // Second run must not resend
        $this->artisan('whatsapp:send-pickup-messages')->assertSuccessful();
        Queue::assertPushed(SendWhatsappMessageJob::class, 1);
    }

    public function test_command_is_scheduled_daily_at_8am(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($e) => str_contains($e->command ?? '', 'whatsapp:send-pickup-messages'));

        $this->assertNotNull($event);
        $this->assertSame('0 8 * * *', $event->expression);
    }
}
