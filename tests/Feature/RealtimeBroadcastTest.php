<?php

namespace Tests\Feature;

use App\Events\RealtimeChanged;
use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\FinancialLedgerEntry;
use App\Models\User;
use App\Realtime\RealtimeHub;
use App\Realtime\RealtimePages;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeBroadcastTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $clientUser;
    protected ClientProfile $client;
    protected User $driver;
    protected DriverProfile $driverProfile;
    protected City $city;
    protected Area $area;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key'    => 'test-key',
            'broadcasting.connections.pusher.secret' => 'test-secret',
            'broadcasting.connections.pusher.app_id' => '1',
        ]);
        require base_path('routes/channels.php'); // register channels on the pusher broadcaster

        $this->admin      = $this->makeUser('admin');
        $this->clientUser = $this->makeUser('client_master');
        $this->city       = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area       = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        $this->client = ClientProfile::create([
            'master_user_id' => $this->clientUser->id,
            'company_name'   => 'Test Merchant',
            'city_id'        => $this->city->id,
            'area_id'        => $this->area->id,
            'status'         => 'active',
        ]);

        $this->driver        = $this->makeUser('driver');
        $this->driverProfile = DriverProfile::create([
            'user_id'             => $this->driver->id,
            'national_id'         => '1234567890',
            'license_number'      => 'L-1234',
            'license_expiry_date' => now()->addYear(),
        ]);

        $this->resetBroadcasts();
    }

    /** Drop anything buffered / sent by fixtures so assertions only see the action under test. */
    private function resetBroadcasts(): void
    {
        Event::fake([RealtimeChanged::class]);
        app(RealtimeHub::class)->flush();
        Event::fake([RealtimeChanged::class]);
    }

    private function createOrder(): \App\Models\Order
    {
        return app(OrderService::class)->createOrder([
            'client_profile_id' => $this->client->id,
            'payment_type'      => 'cod',
            'order_price'       => 100.00,
            'receiver_name'     => 'Receiver',
            'receiver_phone'    => '0790000001',
            'city_id'           => $this->city->id,
            'area_id'           => $this->area->id,
            'address_text'      => '123 Abdali St',
        ], $this->clientUser);
    }

    private function makeUser(string $role): User
    {
        static $seq = 0;

        return User::factory()->create(['role' => $role, 'phone' => '07900' . str_pad((string) ++$seq, 5, '0', STR_PAD_LEFT)]);
    }

    private function sentTo(string $channel): array
    {
        return Event::dispatched(RealtimeChanged::class, fn (RealtimeChanged $e) => $e->channel === $channel)
            ->flatMap(fn ($args) => $args[0]->changes)
            ->all();
    }

    public function test_client_order_is_pushed_to_admins_and_that_client_only(): void
    {
        $otherClient = ClientProfile::create([
            'master_user_id' => $this->makeUser('client_master')->id,
            'company_name'   => 'Other Merchant',
            'city_id'        => $this->city->id,
            'area_id'        => $this->area->id,
            'status'         => 'active',
        ]);
        $this->resetBroadcasts();

        $order = $this->createOrder();

        Event::assertNotDispatched(RealtimeChanged::class); // buffered until the request ends

        app(RealtimeHub::class)->flush();

        $admin = collect($this->sentTo('admin.realtime'));
        $this->assertTrue($admin->contains(fn ($c) => $c['type'] === 'order' && $c['id'] === $order->id && $c['action'] === 'created'));

        $client = collect($this->sentTo('client.' . $this->client->id));
        $this->assertTrue($client->contains(fn ($c) => $c['type'] === 'order' && $c['id'] === $order->id));

        $this->assertSame([], $this->sentTo('client.' . $otherClient->id));
    }

    public function test_reassigning_an_order_notifies_both_drivers(): void
    {
        $order = $this->createOrder();
        $order->update(['driver_profile_id' => $this->driverProfile->id]);

        $secondDriver  = $this->makeUser('driver');
        $secondProfile = DriverProfile::create([
            'user_id' => $secondDriver->id, 'national_id' => '2', 'license_number' => 'L-2', 'license_expiry_date' => now()->addYear(),
        ]);
        $this->resetBroadcasts();

        $order->update(['driver_profile_id' => $secondProfile->id]);
        app(RealtimeHub::class)->flush();

        $this->assertNotEmpty($this->sentTo('driver.' . $this->driver->id));
        $this->assertNotEmpty($this->sentTo('driver.' . $secondDriver->id));
    }

    public function test_driver_financial_record_reaches_admins_client_and_driver(): void
    {
        $order = $this->createOrder();
        $this->resetBroadcasts();

        $entry = FinancialLedgerEntry::create([
            'order_id'          => $order->id,
            'client_profile_id' => $this->client->id,
            'driver_id'         => $this->driver->id,
            'from_account'      => 'driver',
            'to_account'        => 'company',
            'amount'            => 25,
            'type'              => 'driver_settlement',
            'recorded_by'       => $this->driver->id,
        ]);

        app(RealtimeHub::class)->flush();

        $expected = ['type' => 'financial_ledger_entry', 'id' => $entry->id, 'action' => 'created'];
        $matches  = fn (string $channel) => collect($this->sentTo($channel))->contains(fn ($c) => array_intersect_assoc($expected, $c) === $expected);

        $this->assertTrue($matches('admin.realtime'));
        $this->assertTrue($matches('client.' . $this->client->id));
        $this->assertTrue($matches('driver.' . $this->driver->id));
    }

    public function test_rolled_back_changes_are_not_broadcast(): void
    {
        $order = $this->createOrder();
        $this->resetBroadcasts();

        DB::beginTransaction();
        FinancialLedgerEntry::create([
            'order_id' => $order->id, 'client_profile_id' => $this->client->id, 'driver_id' => $this->driver->id, 'from_account' => 'driver', 'to_account' => 'company',
            'amount' => 5, 'type' => 'driver_settlement', 'recorded_by' => $this->admin->id,
        ]);
        DB::rollBack();

        app(RealtimeHub::class)->flush();

        Event::assertNotDispatched(RealtimeChanged::class);
    }

    public function test_gps_pings_do_not_trigger_refreshes(): void
    {
        $this->driverProfile->update([
            'current_latitude'    => 31.95,
            'current_longitude'   => 35.91,
            'location_updated_at' => now(),
        ]);

        app(RealtimeHub::class)->flush();

        Event::assertNotDispatched(RealtimeChanged::class);
    }

    public function test_private_channel_authorization(): void
    {
        $post = fn (string $channel) => ['socket_id' => '1234.5678', 'channel_name' => 'private-' . $channel];

        // Dashboards (session)
        $this->actingAs($this->admin)->post('/broadcasting/auth', $post('admin.realtime'))->assertOk();
        $this->actingAs($this->clientUser)->post('/broadcasting/auth', $post('admin.realtime'))->assertForbidden();
        $this->actingAs($this->clientUser)->post('/broadcasting/auth', $post('client.' . $this->client->id))->assertOk();
        $this->actingAs($this->clientUser)->post('/broadcasting/auth', $post('client.' . ($this->client->id + 99)))->assertForbidden();

        // Mobile app (Sanctum bearer token)
        $this->app['auth']->forgetGuards(); // drop the session user from the requests above
        $token = $this->driver->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson('/api/broadcasting/auth', $post('driver.' . $this->driver->id))
            ->assertOk()->assertJsonStructure(['auth']);
        $this->withToken($token)->postJson('/api/broadcasting/auth', $post('driver.' . $this->admin->id))->assertForbidden();
    }

    public function test_page_rules(): void
    {
        $this->assertNull(RealtimePages::typesFor('admin.orders.create'));
        $this->assertNull(RealtimePages::typesFor('admin.cms.faqs.index'));
        $this->assertSame(['*'], RealtimePages::typesFor('admin.dashboard'));
        $this->assertContains('order', RealtimePages::typesFor('admin.orders.index'));
        $this->assertContains('financial_ledger_entry', RealtimePages::typesFor('admin.financials.index'));

        $this->assertSame('admin.realtime', RealtimePages::channelFor($this->admin));
        $this->assertSame('client.' . $this->client->id, RealtimePages::channelFor($this->clientUser));
    }

    public function test_dashboard_pages_load_the_realtime_script_except_forms(): void
    {
        $this->actingAs($this->clientUser)->get(route('client.orders.index'))
            ->assertOk()
            ->assertSee('window.SaeeRealtimeConfig', false)
            ->assertSee('"channel":"client.' . $this->client->id . '"', false)
            ->assertSee('js/realtime.js', false);

        $this->actingAs($this->clientUser)->get(route('client.orders.create'))
            ->assertOk()
            ->assertDontSee('js/realtime.js', false);
    }
}
