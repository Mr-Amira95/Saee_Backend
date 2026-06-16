<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\City;
use App\Models\Area;
use App\Models\Order;
use App\Models\Attendance;
use App\Models\DriverRating;
use App\Models\OrderTrackingLog;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceAndPerformanceTest extends TestCase
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
    }

    public function test_user_can_check_in_and_check_out()
    {
        $this->actingAs($this->admin);

        // 1. Post Check-in
        $response = $this->post(route('admin.attendance.check-in'), [
            'location' => '31.9566,35.9114'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $attendance = Attendance::where('user_id', $this->admin->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals(now()->toDateString(), $attendance->date->toDateString());
        $this->assertEquals('31.9566,35.9114', $attendance->check_in_location);


        // 2. Post Check-out
        $response2 = $this->post(route('admin.attendance.check-out'), [
            'location' => '31.9570,35.9120'
        ]);

        $response2->assertStatus(200);
        $response2->assertJson(['success' => true]);

        $log = Attendance::where('user_id', $this->admin->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $this->assertNotNull($log->check_out_at);
        $this->assertEquals('31.9570,35.9120', $log->check_out_location);
    }

    public function test_customer_can_submit_rating_for_completed_order()
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

        // Deliver order
        $this->orderService->updateStatus($order, 'delivered', [], $this->admin);

        // GET public feedback view
        $response = $this->get(route('public.share-location', $order->order_number));
        $response->assertStatus(200);
        $response->assertSee('Rate Your Delivery');
        $response->assertSee($order->order_number);

        // POST rating feedback submission
        $postResponse = $this->post(route('public.share-location.update', $order->order_number), [
            'rating' => 5,
            'comment' => 'Excellent delivery service!',
        ]);

        $postResponse->assertStatus(200);
        $postResponse->assertJson(['success' => true]);

        $this->assertDatabaseHas('driver_ratings', [
            'order_id' => $order->id,
            'driver_id' => $this->driver->id,
            'rating' => 5,
            'comment' => 'Excellent delivery service!',
        ]);

        // Eager load and assert relationship
        $this->assertEquals(5, $order->fresh()->driverRating->rating);
    }

    public function test_driver_performance_calculations()
    {
        $order1 = $this->orderService->createOrder([
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

        $order2 = $this->orderService->createOrder([
            'client_profile_id' => $this->client->id,
            'driver_id' => $this->driver->id,
            'payment_type' => 'cod',
            'order_price' => 80.00,
            'receiver_name' => 'Jane Smith',
            'receiver_phone' => '0790000002',
            'city_id' => $this->city->id,
            'area_id' => $this->area->id,
            'address_text' => '456 Abdali St',
        ], $this->admin);

        // 1. Deliver order 1
        $this->orderService->updateStatus($order1, 'delivered', [], $this->admin);
        
        // 2. Reject order 2
        $this->orderService->updateStatus($order2, 'rejected', [], $this->admin);

        // Assert delivery success rate calculation: 1 out of 2 = 50.0%
        $this->assertEquals(50.0, $this->driver->fresh()->delivery_success_rate);

        // 3. Submit Ratings: Rate 4 stars for order 1
        DriverRating::create([
            'order_id' => $order1->id,
            'driver_id' => $this->driver->id,
            'rating' => 4,
            'comment' => 'Good job',
        ]);

        // Assert average rating calculation: 4.0 ★
        $this->assertEquals(4.0, $this->driver->fresh()->average_rating);

        // 4. Test transit duration calculation
        // Ensure tracking logs exist
        OrderTrackingLog::query()->delete();
        
        $pickupTime = now()->subHours(3);
        $deliveryTime = now();

        $log1 = new OrderTrackingLog([
            'order_id' => $order1->id,
            'user_id' => $this->admin->id,
            'from_status' => 'pending',
            'to_status' => 'picked_up',
            'description' => 'Picked up',
        ]);
        $log1->created_at = $pickupTime;
        $log1->save();

        $log2 = new OrderTrackingLog([
            'order_id' => $order1->id,
            'user_id' => $this->admin->id,
            'from_status' => 'picked_up',
            'to_status' => 'delivered',
            'description' => 'Delivered',
        ]);
        $log2->created_at = $deliveryTime;
        $log2->save();

        // Assert average transit hours is 3.0 hours
        $this->assertEquals(3.0, $this->driver->fresh()->average_transit_hours);
    }
}
