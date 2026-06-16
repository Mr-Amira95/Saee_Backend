<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\City;
use App\Models\Area;
use App\Models\RejectionReason;
use App\Services\OrderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class OrderTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(WhatsAppTemplateSeeder::class);

        // 1. Ensure Jordan cities exist
        if (City::count() === 0) {
            $this->call(JordanCitiesSeeder::class);
        }

        $city = City::first();
        $area = Area::where('city_id', $city->id)->first();

        // Ensure rejection reason exists
        if (RejectionReason::count() === 0) {
            RejectionReason::create(['reason' => 'Customer refused to pay', 'reason_ar' => 'العميل رفض الدفع', 'is_active' => true]);
            RejectionReason::create(['reason' => 'Wrong delivery address', 'reason_ar' => 'عنوان التسليم خاطئ', 'is_active' => true]);
            RejectionReason::create(['reason' => 'No response from customer', 'reason_ar' => 'لا يوجد استجابة من العميل', 'is_active' => true]);
        }

        // 2. Create Test Client
        $clientUser = User::firstOrCreate(
            ['email' => 'merchant@saeelogistics.com'],
            [
                'name' => 'Speedy Merchant Co.',
                'phone' => '0599999991',
                'password' => Hash::make('password123'),
                'role' => 'client_master',
                'status' => 'active'
            ]
        );

        $clientProfile = ClientProfile::firstOrCreate(
            ['master_user_id' => $clientUser->id],
            [
                'company_name' => 'Speedy Merchant Co.',
                'city_id' => $city->id,
                'area_id' => $area->id,
                'status' => 'active',
                'balance' => 0.00,
                'credit_limit' => 5000.00
            ]
        );

        // 3. Create Test Driver
        $driverUser = User::firstOrCreate(
            ['email' => 'driver1@saeelogistics.com'],
            [
                'name' => 'Ahmed Driver',
                'phone' => '0599999992',
                'password' => Hash::make('password123'),
                'role' => 'driver',
                'status' => 'active'
            ]
        );

        DriverProfile::firstOrCreate(
            ['user_id' => $driverUser->id],
            [
                'national_id' => '2000000001',
                'license_number' => 'DL-9921',
                'license_expiry_date' => now()->addYears(2),
                'is_available' => true
            ]
        );

        // Get admin user to log actions
        $admin = User::where('role', 'superadmin')->first();
        if (!$admin) {
            $admin = User::firstOrCreate(
                ['email' => 'admin@saeelogistics.com'],
                [
                    'name' => 'Admin User',
                    'phone' => '0599999990',
                    'password' => Hash::make('password123'),
                    'role' => 'admin',
                    'status' => 'active'
                ]
            );
        }

        // 4. Seed Orders using OrderService to generate financials
        $orderService = app(OrderService::class);

        // Clear existing orders to prevent seeder bloat
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('orders')->truncate();
        DB::table('order_tracking_logs')->truncate();
        DB::table('financial_ledger_entries')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Order 1: Pending order
        $orderService->createOrder([
            'client_profile_id' => $clientProfile->id,
            'driver_id' => null,
            'order_description' => 'Cotton T-Shirts Pack',
            'payment_type' => 'cod',
            'delivery_on_customer' => false,
            'order_price' => 250.00,
            'receiver_name' => 'Samer Abdallah',
            'receiver_phone' => '0791234567',
            'city_id' => $city->id,
            'area_id' => $area->id,
            'address_text' => 'Building 14, 2nd floor, Abdali Street',
            'notes' => 'Call before delivery please.'
        ], $admin);

        // Order 2: Picked Up / In Transit
        $o2 = $orderService->createOrder([
            'client_profile_id' => $clientProfile->id,
            'driver_id' => $driverUser->id,
            'order_description' => 'Bluetooth Headphones',
            'payment_type' => 'cod',
            'delivery_on_customer' => true,
            'delivery_customer_amount' => 15.00,
            'order_price' => 85.00,
            'receiver_name' => 'Lina Qasem',
            'receiver_phone' => '0787654321',
            'city_id' => $city->id,
            'area_id' => $area->id,
            'address_text' => 'Gardens District, Behind Jordan Bank',
        ], $admin);
        $orderService->updateStatus($o2, 'picked_up', ['driver_id' => $driverUser->id], $admin);

        // Order 3: Delivered (COD, shipping paid by client) - Driver holds cash
        $o3 = $orderService->createOrder([
            'client_profile_id' => $clientProfile->id,
            'driver_id' => $driverUser->id,
            'order_description' => 'Leather Wallet',
            'payment_type' => 'cod',
            'delivery_on_customer' => false,
            'order_price' => 45.00,
            'receiver_name' => 'Yanal Haddad',
            'receiver_phone' => '0772223334',
            'city_id' => $city->id,
            'area_id' => $area->id,
            'address_text' => 'Sweifieh, Al-Hamra Street',
        ], $admin);
        $orderService->updateStatus($o3, 'picked_up', ['driver_id' => $driverUser->id], $admin);
        $orderService->updateStatus($o3, 'delivered', [], $admin);

        // Order 4: Delivered (COD, shipping paid by customer) - Driver holds cash
        $o4 = $orderService->createOrder([
            'client_profile_id' => $clientProfile->id,
            'driver_id' => $driverUser->id,
            'order_description' => 'Coffee Maker Machine',
            'payment_type' => 'cod',
            'delivery_on_customer' => true,
            'delivery_customer_amount' => 20.00,
            'order_price' => 320.00,
            'receiver_name' => 'Firas Naber',
            'receiver_phone' => '0799988776',
            'city_id' => $city->id,
            'area_id' => $area->id,
            'address_text' => 'Khalda, near English School',
        ], $admin);
        $orderService->updateStatus($o4, 'picked_up', ['driver_id' => $driverUser->id], $admin);
        $orderService->updateStatus($o4, 'delivered', [], $admin);

        // Order 5: Rejected order
        $o5 = $orderService->createOrder([
            'client_profile_id' => $clientProfile->id,
            'driver_id' => $driverUser->id,
            'order_description' => 'Summer Dress',
            'payment_type' => 'cod',
            'delivery_on_customer' => false,
            'order_price' => 120.00,
            'receiver_name' => 'Rania Awad',
            'receiver_phone' => '0785554433',
            'city_id' => $city->id,
            'area_id' => $area->id,
            'address_text' => 'Tabarbour, Abu Alia District',
        ], $admin);
        $orderService->updateStatus($o5, 'picked_up', ['driver_id' => $driverUser->id], $admin);
        $orderService->updateStatus($o5, 'rejected', [
            'rejection_reason_id' => RejectionReason::first()->id,
            'notes' => 'Customer said she ordered a different color.'
        ], $admin);
    }
}
