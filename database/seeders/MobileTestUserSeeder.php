<?php

namespace Database\Seeders;

use App\Models\ClientProfile;
use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds / resets the two mobile test accounts to use password: "password"
 *
 * Run: php artisan db:seed --class=MobileTestUserSeeder
 *
 * Test accounts:
 *   client  → phone: 0599999991  email: merchant@saeelogistics.com  password: password
 *   driver  → phone: 0599999992  email: driver1@saeelogistics.com   password: password
 */
class MobileTestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure cities exist so client profile FK constraints pass
        if (\App\Models\City::count() === 0) {
            $this->call(JordanCitiesSeeder::class);
        }

        $city = \App\Models\City::first();
        $area = \App\Models\Area::where('city_id', $city->id)->first();

        // ── Client Master ──────────────────────────────────────────────────
        $clientUser = User::updateOrCreate(
            ['email' => 'merchant@saeelogistics.com'],
            [
                'name'     => 'Speedy Merchant Co.',
                'phone'    => '0599999991',
                'password' => Hash::make('password'),
                'role'     => 'client_master',
                'status'   => 'active',
            ]
        );

        ClientProfile::firstOrCreate(
            ['master_user_id' => $clientUser->id],
            [
                'company_name' => 'Speedy Merchant Co.',
                'city_id'      => $city->id,
                'area_id'      => $area?->id,
                'status'       => 'active',
                'balance'      => 0.00,
                'credit_limit' => 5000.00,
            ]
        );

        // ── Driver ─────────────────────────────────────────────────────────
        $driverUser = User::updateOrCreate(
            ['email' => 'driver1@saeelogistics.com'],
            [
                'name'     => 'Ahmed Driver',
                'phone'    => '0599999992',
                'password' => Hash::make('password'),
                'role'     => 'driver',
                'status'   => 'active',
            ]
        );

        DriverProfile::firstOrCreate(
            ['user_id' => $driverUser->id],
            [
                'national_id'         => '2000000001',
                'license_number'      => 'DL-9921',
                'license_expiry_date' => now()->addYears(2)->toDateString(),
                'is_available'        => true,
            ]
        );

        $this->command->info('Mobile test users seeded with password: password');
    }
}
