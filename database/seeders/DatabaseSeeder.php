<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\RejectionReason;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedAdminUser();
        $this->seedRejectionReasons();
        $this->seedJordanCitiesAndAreas();
        $this->call(WebsiteContentSeeder::class);
    }

    private function seedAdminUser(): void
    {
        User::updateOrCreate(
            ['email' => 'hussam.admin@gmail.com'],
            [
                'name'                   => 'Hussam Admin',
                'username'               => 'hussam.admin',
                'phone'                  => '799999999',
                'phone_country_code'     => '+962',
                'otp_channel'            => 'email',
                'email_verified_at'      => now(),
                'role'                   => 'superadmin',
                'status'                 => 'active',
                'notifications_enabled'  => true,
                'password'               => Hash::make('Pass@123'),
            ]
        );
    }

    private function seedRejectionReasons(): void
    {
        $reasons = [
            ['reason' => 'Recipient not available',                  'reason_ar' => 'المستلم غير متوفر'],
            ['reason' => 'Incorrect delivery address',                'reason_ar' => 'عنوان التسليم غير صحيح'],
            ['reason' => 'Recipient refused the shipment',            'reason_ar' => 'المستلم رفض استلام الشحنة'],
            ['reason' => 'Customer requested delivery cancellation',  'reason_ar' => 'طلب العميل إلغاء التسليم'],
            ['reason' => 'Unable to contact recipient',               'reason_ar' => 'تعذر التواصل مع المستلم'],
            ['reason' => 'Delivery location is inaccessible',         'reason_ar' => 'موقع التسليم غير قابل للوصول'],
            ['reason' => 'Shipment damaged during delivery',          'reason_ar' => 'الشحنة تعرضت للتلف أثناء التوصيل'],
            ['reason' => 'Payment not completed (Cash on Delivery)',  'reason_ar' => 'لم يتم إتمام الدفع (الدفع عند الاستلام)'],
            ['reason' => 'Delivery delayed due to weather conditions', 'reason_ar' => 'تأخر التسليم بسبب الظروف الجوية'],
            ['reason' => 'Other',                                     'reason_ar' => 'أخرى'],
        ];

        foreach ($reasons as $reason) {
            RejectionReason::updateOrCreate(
                ['reason' => $reason['reason']],
                ['reason_ar' => $reason['reason_ar'], 'is_active' => true]
            );
        }
    }

    private function seedJordanCitiesAndAreas(): void
    {
        $cities = [
            'Amman'            => ['name_ar' => 'عمّان',           'areas' => ['Downtown (Al-Balad)' => 'وسط البلد', 'Abdali' => 'العبدلي', 'Shmeisani' => 'الشميساني', 'Jabal Amman' => 'جبل عمان', 'Sweifieh' => 'الصويفية', 'Khalda' => 'خلدا', 'Tlaa Al-Ali' => 'تلاع العلي', 'Jubeiha' => 'الجبيهة', 'Marka' => 'ماركا', 'Al-Nuzha' => 'النزهة']],
            'Zarqa'            => ['name_ar' => 'الزرقاء',         'areas' => ['Zarqa City Center' => 'مركز مدينة الزرقاء', 'New Zarqa' => 'الزرقاء الجديدة', 'Hashimiyya' => 'الهاشمية', 'Al-Dhuleil' => 'الضليل', 'Hallabat' => 'الحلابات']],
            'Irbid'            => ['name_ar' => 'إربد',            'areas' => ['Irbid City Center' => 'مركز مدينة إربد', 'Downtown Irbid' => 'وسط إربد', 'University District' => 'منطقة الجامعة', 'Al-Hashmi' => 'الهاشمي', 'Al-Muhajireen' => 'المهاجرين']],
            'Aqaba'            => ['name_ar' => 'العقبة',          'areas' => ['Aqaba City Center' => 'مركز مدينة العقبة', 'Tourist Area' => 'المنطقة السياحية', 'Port Area' => 'منطقة الميناء', 'Industrial Zone' => 'المنطقة الصناعية', 'ASEZA Zone' => 'منطقة العقبة الاقتصادية']],
            'Mafraq'           => ['name_ar' => 'المفرق',          'areas' => ['Mafraq City Center' => 'مركز مدينة المفرق', 'Badia' => 'البادية', 'Safawi' => 'الصفاوي', 'Ruweished' => 'الرويشد']],
            'Jerash'           => ['name_ar' => 'جرش',             'areas' => ['Jerash City Center' => 'مركز مدينة جرش', 'Sakeb' => 'ساكب', 'Burma' => 'برما']],
            'Ajloun'           => ['name_ar' => 'عجلون',           'areas' => ['Ajloun City Center' => 'مركز مدينة عجلون', 'Anjara' => 'عنجرة', 'Ayn Janna' => 'عين جنا']],
            'Madaba'           => ['name_ar' => 'مادبا',           'areas' => ['Madaba City Center' => 'مركز مدينة مادبا', 'Libb' => 'لبن', 'Maeen' => 'ماعين']],
            'Salt'             => ['name_ar' => 'السلط',           'areas' => ['Salt City Center' => 'مركز مدينة السلط', 'Ain Al-Basha' => 'عين الباشا', 'Al-Salalim' => 'السلالم']],
            'Karak'            => ['name_ar' => 'الكرك',           'areas' => ['Karak City Center' => 'مركز مدينة الكرك', 'Qatraneh' => 'القطرانة', 'Mazar' => 'المزار', 'Al-Mutah' => 'مؤتة']],
            'Tafilah'          => ['name_ar' => 'الطفيلة',         'areas' => ['Tafilah City Center' => 'مركز مدينة الطفيلة', 'Buseira' => 'بصيرا', 'Sela' => 'السلع']],
            "Ma'an"            => ['name_ar' => 'معان',            'areas' => ["Ma'an City Center" => 'مركز مدينة معان', 'Al-Jafr' => 'الجفر', 'Al-Husseiniya' => 'الحسينية']],
            'Ramtha'           => ['name_ar' => 'الرمثا',          'areas' => ['Ramtha City Center' => 'مركز مدينة الرمثا', 'Al-Hoshiyeh' => 'الهوشية', 'Al-Yarmouk' => 'اليرموك']],
            'Sahab'            => ['name_ar' => 'سحاب',            'areas' => ['Sahab City Center' => 'مركز مدينة سحاب', 'Sahab Industrial City' => 'مدينة سحاب الصناعية']],
            'Fuheis'           => ['name_ar' => 'الفحيص',          'areas' => ['Fuheis City Center' => 'مركز مدينة الفحيص', 'Mahis' => 'ماحص']],
            'Kufranjah'        => ['name_ar' => 'كفرنجة',          'areas' => ['Kufranjah City Center' => 'مركز مدينة كفرنجة']],
            'Al-Jiza'          => ['name_ar' => 'الجيزة',          'areas' => ['Al-Jiza City Center' => 'مركز مدينة الجيزة']],
            'Shobak'           => ['name_ar' => 'الشوبك',          'areas' => ['Shobak City Center' => 'مركز مدينة الشوبك']],
            'Dhiban'           => ['name_ar' => 'ذيبان',           'areas' => ['Dhiban City Center' => 'مركز مدينة ذيبان']],
            'Azraq'            => ['name_ar' => 'الأزرق',          'areas' => ['Azraq City Center' => 'مركز مدينة الأزرق', 'Azraq Shomali' => 'الأزرق الشمالي', 'Azraq Janoubi' => 'الأزرق الجنوبي']],
            'Deir Alla'        => ['name_ar' => 'دير علا',         'areas' => ['Deir Alla City Center' => 'مركز مدينة دير علا']],
            'Southern Shuna'   => ['name_ar' => 'الشونة الجنوبية', 'areas' => ['Southern Shuna City Center' => 'مركز الشونة الجنوبية']],
            'Northern Shuna'   => ['name_ar' => 'الشونة الشمالية', 'areas' => ['Northern Shuna City Center' => 'مركز الشونة الشمالية']],
            'Ar-Rusayfah'      => ['name_ar' => 'الرصيفة',         'areas' => ['Ar-Rusayfah City Center' => 'مركز مدينة الرصيفة', 'Al-Ghabawi' => 'الغباوي']],
            'Umm Qais'         => ['name_ar' => 'أم قيس',          'areas' => ['Umm Qais City Center' => 'مركز مدينة أم قيس']],
            'Al-Husn'          => ['name_ar' => 'الحصن',           'areas' => ['Al-Husn City Center' => 'مركز مدينة الحصن']],
            'Bani Kinanah'     => ['name_ar' => 'بني كنانة',       'areas' => ['Bani Kinanah City Center' => 'مركز بني كنانة', 'Al-Tayba' => 'الطيبة']],
            'Wadi Musa'        => ['name_ar' => 'وادي موسى',       'areas' => ['Wadi Musa City Center' => 'مركز مدينة وادي موسى']],
            'Petra'            => ['name_ar' => 'البتراء',         'areas' => ['Petra Archaeological Area' => 'المنطقة الأثرية للبتراء', 'Umm Sayhoun' => 'أم صيحون']],
            'Al-Quweirah'      => ['name_ar' => 'القويرة',         'areas' => ['Al-Quweirah City Center' => 'مركز مدينة القويرة']],
        ];

        foreach ($cities as $name => $data) {
            $city = City::updateOrCreate(
                ['name' => $name],
                [
                    'name_ar'        => $data['name_ar'],
                    'country_code'   => 'jo',
                    'is_active'      => true,
                    'delivery_price' => 3,
                ]
            );

            foreach ($data['areas'] as $areaName => $areaNameAr) {
                Area::updateOrCreate(
                    ['city_id' => $city->id, 'name' => $areaName],
                    ['name_ar' => $areaNameAr, 'is_active' => true]
                );
            }
        }
    }

    private function seedPermissions(): void
    {
        $now = now();

        // Admin permissions — one page-level permission per admin-panel page
        // (name = page slug), plus one action-level permission per granular
        // action on that page (name = "<page>.<action>"). A page permission
        // alone makes the page visible (read-only if it has action rows and
        // none are granted); superadmins bypass this system entirely.
        $adminPages = [
            'clients'           => ['label' => 'Clients',            'actions' => [
                'add' => 'Add Client', 'delete' => 'Delete Client', 'edit' => 'Edit Client',
                'reset_password' => 'Reset Password', 'bank_details' => 'Show Bank Details',
            ]],
            'drivers'           => ['label' => 'Drivers',            'actions' => [
                'add' => 'Add Driver', 'delete' => 'Delete Driver', 'edit' => 'Edit Driver',
                'reset_password' => 'Reset Password', 'bank_details' => 'Show Bank Details', 'live_map' => 'Show Live Map',
            ]],
            'admins'            => ['label' => 'Admins',             'actions' => [
                'add' => 'Add Admin', 'delete' => 'Delete Admin', 'edit' => 'Edit Admin', 'reset_password' => 'Reset Password',
            ]],
            'orders'            => ['label' => 'Orders',             'actions' => [
                'add' => 'Add Order', 'edit' => 'Edit Order', 'delete' => 'Delete Order',
                'import' => 'Import CSV & Import Image', 'assign_driver' => 'Assign Driver',
            ]],
            'support'           => ['label' => 'Support Ticket',    'actions' => [
                'open_ticket' => 'Open Ticket', 'reply' => 'Reply', 'resolve' => 'Resolve Ticket (Close)',
            ]],
            'ai_conversations'  => ['label' => 'AI Conversation',    'actions' => [
                'delete' => 'Delete AI Conversation',
            ]],
            'reports'           => ['label' => 'Reports',            'actions' => [
                'center' => 'View Reports Center', 'kpi_insights' => 'View KPI Insights', 'rating' => 'View Rating',
            ]],
            'cities'            => ['label' => 'Cities',             'actions' => [
                'add' => 'Add City & Area', 'edit' => 'Edit City & Areas', 'delete' => 'Delete City & Areas', 'activate' => 'Activate City & Areas',
            ]],
            'rejection_reasons' => ['label' => 'Rejection Reasons',  'actions' => [
                'add' => 'Add Rejection Reason', 'edit' => 'Edit Rejection Reason', 'delete' => 'Delete Rejection Reason', 'activate' => 'Activate Rejection Reason',
            ]],
            'finances'          => ['label' => 'Finances',           'actions' => [
                'settlements' => 'Settlements', 'checkout_approvals' => 'Checkout Approvals', 'cod_invoices' => 'COD Invoices',
                'reconciliation' => 'Reconciliation', 'driver_payroll' => 'Driver Payroll', 'client_billing' => 'Client Billing', 'expenses' => 'Expenses',
            ]],
            'cms'               => ['label' => 'Control Website CMS', 'actions' => []],
            'attendance'        => ['label' => 'View Attendance',    'actions' => []],
            'notifications'     => ['label' => 'View Notifications Center', 'actions' => []],
        ];

        $permissions = [];
        foreach ($adminPages as $slug => $page) {
            $permissions[] = ['name' => $slug, 'display_name' => $page['label'], 'scope' => 'admin', 'group' => $slug];
            foreach ($page['actions'] as $actionSlug => $actionLabel) {
                $permissions[] = ['name' => "{$slug}.{$actionSlug}", 'display_name' => $actionLabel, 'scope' => 'admin', 'group' => $slug];
            }
        }

        // Drop the old flat admin permissions (and any stale page/action rows)
        // that no longer exist in the page + sub-action model above.
        $adminPermissionNames = array_column($permissions, 'name');
        DB::table('permissions')->where('scope', 'admin')->whereNotIn('name', $adminPermissionNames)->delete();

        $permissions = [
            ...$permissions,

            // Client permissions — one page-level permission per client-portal page.
            // Having the permission grants full access to everything inside that page.
            ['name' => 'orders',           'display_name' => 'Orders',           'scope' => 'client', 'group' => 'orders'],
            ['name' => 'support',          'display_name' => 'Support',          'scope' => 'client', 'group' => 'support'],
            ['name' => 'payout_invoices',  'display_name' => 'Payout Invoices',  'scope' => 'client', 'group' => 'payout_invoices'],
            ['name' => 'billing',          'display_name' => 'Billing',          'scope' => 'client', 'group' => 'billing'],
            ['name' => 'reports',          'display_name' => 'Reports',          'scope' => 'client', 'group' => 'reports'],
            ['name' => 'team',             'display_name' => 'Team',             'scope' => 'client', 'group' => 'team'],
            ['name' => 'account',          'display_name' => 'Account',          'scope' => 'client', 'group' => 'account'],
            ['name' => 'ai_assistant',     'display_name' => 'AI Assistant',     'scope' => 'client', 'group' => 'ai_assistant'],
        ];

        $rows = array_map(fn($p) => array_merge($p, [
            'description' => null,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]), $permissions);

        // Drop old granular client permissions that no longer exist in the simplified,
        // page-level model above (cascades to client_employee_permission_user grants).
        $clientPageNames = array_column(array_filter($permissions, fn($p) => $p['scope'] === 'client'), 'name');
        DB::table('permissions')->where('scope', 'client')->whereNotIn('name', $clientPageNames)->delete();

        DB::table('permissions')->upsert(
            $rows,
            ['name', 'scope'],
            ['display_name', 'group', 'updated_at']
        );
    }
}
