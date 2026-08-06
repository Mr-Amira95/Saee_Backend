<?php

namespace Tests\Feature\Api;

use App\Models\Area;
use App\Models\City;
use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderPdfExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $clientUser1;
    private ClientProfile $clientProfile1;
    private User $clientUser2;
    private ClientProfile $clientProfile2;
    private City $city;
    private Area $area;
    private OrderService $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(\App\Services\SupportNotificationService::class, function ($mock) {
            $mock->shouldIgnoreMissing();
        });

        $this->orderService = app(OrderService::class);

        $this->admin = User::factory()->create([
            'role'   => 'superadmin',
            'status' => 'active',
            'phone'  => '079' . rand(1000000, 9999999),
        ]);

        $this->city = City::create(['name' => 'Amman', 'country_code' => 'JO', 'delivery_price' => 10.00]);
        $this->area = Area::create(['name' => 'Abdali', 'city_id' => $this->city->id]);

        $this->clientUser1 = User::factory()->create([
            'role'   => 'client_master',
            'status' => 'active',
            'phone'  => '079' . rand(1000000, 9999999),
        ]);
        $this->clientProfile1 = ClientProfile::create([
            'master_user_id' => $this->clientUser1->id,
            'company_name'   => 'Merchant One',
            'city_id'        => $this->city->id,
            'area_id'        => $this->area->id,
            'status'         => 'active',
        ]);

        $this->clientUser2 = User::factory()->create([
            'role'   => 'client_master',
            'status' => 'active',
            'phone'  => '079' . rand(1000000, 9999999),
        ]);
        $this->clientProfile2 = ClientProfile::create([
            'master_user_id' => $this->clientUser2->id,
            'company_name'   => 'Merchant Two',
            'city_id'        => $this->city->id,
            'area_id'        => $this->area->id,
            'status'         => 'active',
        ]);
    }

    private function makeOrder(ClientProfile $profile, array $overrides = []): Order
    {
        return $this->orderService->createOrder(array_merge([
            'client_profile_id' => $profile->id,
            'payment_type'      => 'cod',
            'order_price'       => 100.00,
            'receiver_name'     => 'Receiver',
            'receiver_phone'    => '0790000001',
            'city_id'           => $this->city->id,
            'area_id'           => $this->area->id,
            'address_text'      => '123 Test St',
        ], $overrides), $this->admin);
    }

    private function assertNeverHtml($response): void
    {
        $contentType = $response->headers->get('Content-Type');
        $this->assertStringNotContainsString('text/html', (string) $contentType);
        $this->assertStringNotContainsString('<!DOCTYPE', substr($response->getContent(), 0, 20));
    }

    // ── Authentication ──────────────────────────────────────────────────

    public function test_unauthenticated_request_returns_json_401_without_redirect(): void
    {
        $response = $this->withHeaders(['Accept' => 'application/pdf'])
            ->get('/api/client/orders/pdf');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated',
                'code'    => 'UNAUTHENTICATED',
            ]);

        $this->assertNeverHtml($response);
    }

    public function test_request_with_bearer_token_header_authenticates_successfully(): void
    {
        $order = $this->makeOrder($this->clientProfile1);
        $token = $this->clientUser1->createToken('flutter-app')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/pdf',
        ])->get('/api/client/orders/pdf?ids=' . $order->id);

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    // ── Success paths ───────────────────────────────────────────────────

    public function test_authenticated_client_can_export_single_order_pdf(): void
    {
        $order = $this->makeOrder($this->clientProfile1);

        Sanctum::actingAs($this->clientUser1);

        $response = $this->withHeaders(['Accept' => 'application/pdf'])
            ->get('/api/client/orders/pdf?ids=' . $order->id);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition');
        $this->assertStringContainsString('attachment; filename="orders-', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_authenticated_client_can_export_multiple_orders_pdf(): void
    {
        $orderOne = $this->makeOrder($this->clientProfile1);
        $orderTwo = $this->makeOrder($this->clientProfile1);

        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?ids=' . $orderOne->id . ',' . $orderTwo->id);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_no_filters_exports_authenticated_clients_matching_orders(): void
    {
        $this->makeOrder($this->clientProfile1);
        $this->makeOrder($this->clientProfile1);

        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_status_filter_matches_orders(): void
    {
        $delivered = $this->makeOrder($this->clientProfile1);
        $delivered->update(['status' => 'delivered']);
        $this->makeOrder($this->clientProfile1); // stays pending

        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?status=delivered');

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_status_filter_with_no_matches_returns_404(): void
    {
        $this->makeOrder($this->clientProfile1); // pending only

        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?status=cancelled');

        $response->assertStatus(404)->assertJson(['success' => false]);
        $this->assertNeverHtml($response);
    }

    public function test_payment_type_filter_matches_orders(): void
    {
        $this->makeOrder($this->clientProfile1, ['payment_type' => 'prepaid']);

        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?payment_type=prepaid');

        $response->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_from_and_to_date_filters_scope_results(): void
    {
        $order = $this->makeOrder($this->clientProfile1);
        $order->created_at = '2026-01-15 10:00:00';
        $order->save();

        Sanctum::actingAs($this->clientUser1);

        // Range that includes the order
        $inRange = $this->get('/api/client/orders/pdf?from=2026-01-01&to=2026-01-31');
        $inRange->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $inRange->getContent());

        // Range that excludes the order
        $outOfRange = $this->get('/api/client/orders/pdf?from=2026-02-01&to=2026-02-28');
        $outOfRange->assertStatus(404);
        $this->assertNeverHtml($outOfRange);
    }

    // ── Ownership / authorization ───────────────────────────────────────

    public function test_client_cannot_export_another_clients_order(): void
    {
        $foreignOrder = $this->makeOrder($this->clientProfile2);

        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?ids=' . $foreignOrder->id);

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
        $this->assertNeverHtml($response);
    }

    public function test_mixed_own_and_foreign_ids_returns_404_rather_than_partial_pdf(): void
    {
        $ownOrder = $this->makeOrder($this->clientProfile1);
        $foreignOrder = $this->makeOrder($this->clientProfile2);

        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?ids=' . $ownOrder->id . ',' . $foreignOrder->id);

        $response->assertStatus(404);
        $this->assertNeverHtml($response);
    }

    // ── Validation ──────────────────────────────────────────────────────

    public function test_non_integer_ids_returns_422(): void
    {
        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?ids=abc,12');

        $response->assertStatus(422)
            ->assertJson(['message' => 'The given data was invalid.'])
            ->assertJsonValidationErrors(['ids.0']);
        $this->assertNeverHtml($response);
    }

    public function test_invalid_status_value_returns_422(): void
    {
        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?status=not_a_real_status');

        $response->assertStatus(422)
            ->assertJson(['message' => 'The given data was invalid.'])
            ->assertJsonValidationErrors(['status']);
        $this->assertNeverHtml($response);
    }

    public function test_invalid_payment_type_returns_422(): void
    {
        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?payment_type=bitcoin');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payment_type']);
        $this->assertNeverHtml($response);
    }

    public function test_invalid_date_format_returns_422(): void
    {
        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?from=not-a-date');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from']);
        $this->assertNeverHtml($response);
    }

    public function test_to_before_from_returns_422(): void
    {
        Sanctum::actingAs($this->clientUser1);

        $response = $this->get('/api/client/orders/pdf?from=2026-02-01&to=2026-01-01');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to']);
        $this->assertNeverHtml($response);
    }

    // ── Driver / non-client accounts ────────────────────────────────────

    public function test_driver_account_cannot_export_order_pdfs(): void
    {
        $driver = User::factory()->create([
            'role'   => 'driver',
            'status' => 'active',
            'phone'  => '079' . rand(1000000, 9999999),
        ]);

        Sanctum::actingAs($driver);

        $response = $this->get('/api/client/orders/pdf');

        $response->assertStatus(403)->assertJson(['success' => false]);
        $this->assertNeverHtml($response);
    }
}
