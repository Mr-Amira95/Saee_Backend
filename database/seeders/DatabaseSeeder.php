<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\RejectionReason;
use App\Models\User;
use App\Models\WhatsAppTemplate;
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
        $this->seedWhatsAppTemplates();
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

        $permissions = [
            // Admin permissions
            ['name' => 'view_dashboard',           'display_name' => 'View Dashboard',              'scope' => 'admin', 'group' => 'dashboard'],
            ['name' => 'view_all_orders',           'display_name' => 'View All Orders',             'scope' => 'admin', 'group' => 'orders'],
            ['name' => 'manage_orders',             'display_name' => 'Manage Orders',               'scope' => 'admin', 'group' => 'orders'],
            ['name' => 'assign_drivers',            'display_name' => 'Assign Drivers',              'scope' => 'admin', 'group' => 'orders'],
            ['name' => 'view_clients',              'display_name' => 'View Clients',                'scope' => 'admin', 'group' => 'clients'],
            ['name' => 'manage_clients',            'display_name' => 'Manage Clients',              'scope' => 'admin', 'group' => 'clients'],
            ['name' => 'verify_client_documents',   'display_name' => 'Verify Client Documents',    'scope' => 'admin', 'group' => 'clients'],
            ['name' => 'view_drivers',              'display_name' => 'View Drivers',                'scope' => 'admin', 'group' => 'drivers'],
            ['name' => 'manage_drivers',            'display_name' => 'Manage Drivers',              'scope' => 'admin', 'group' => 'drivers'],
            ['name' => 'verify_driver_documents',   'display_name' => 'Verify Driver Documents',    'scope' => 'admin', 'group' => 'drivers'],
            ['name' => 'view_invoices',             'display_name' => 'View Invoices',               'scope' => 'admin', 'group' => 'billing'],
            ['name' => 'manage_invoices',           'display_name' => 'Manage Invoices',             'scope' => 'admin', 'group' => 'billing'],
            ['name' => 'manage_wallet',             'display_name' => 'Manage Client Wallets',       'scope' => 'admin', 'group' => 'billing'],
            ['name' => 'view_reports',              'display_name' => 'View Reports',                'scope' => 'admin', 'group' => 'reports'],
            ['name' => 'manage_admins',             'display_name' => 'Manage Admins',               'scope' => 'admin', 'group' => 'admin_mgmt'],
            ['name' => 'grant_admin_permissions',   'display_name' => 'Grant Admin Permissions',    'scope' => 'admin', 'group' => 'admin_mgmt'],
            ['name' => 'view_system_logs',          'display_name' => 'View System Logs',            'scope' => 'admin', 'group' => 'system'],
            ['name' => 'manage_system_settings',    'display_name' => 'Manage System Settings',     'scope' => 'admin', 'group' => 'system'],

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

    private function seedWhatsAppTemplates(): void
    {
        $templates = [
            [
                'event'         => 'order_created',
                'template_body' => "Hello {{customer_name}}, your order #{{order_number}} has been created and assigned to {{driver_name}} (Phone: {{driver_phone}}). Please share your location here: {{location_link}}",
            ],
            [
                'event'         => 'order_picked_up',
                'template_body' => "Hello {{customer_name}},\n\nYour order #{{order_number}} has been picked up by our driver {{driver_name}} 🚚\n\nPlease share your current location so we can deliver your package efficiently!\n\nThank you for choosing SAEE.",
            ],
            [
                'event'         => 'order_delivered',
                'template_body' => "Hello {{customer_name}}, your order #{{order_number}} has been delivered successfully by {{driver_name}}! Thank you for choosing SAEE.",
            ],
            [
                'event'         => 'order_rejected',
                'template_body' => "Hello {{customer_name}}, your order #{{order_number}} could not be delivered. Reason: {{rejection_reason}}. Please review and update your details here: {{location_link}}",
            ],
            [
                'event'         => 'user_invitation',
                'template_body' => "Welcome to Sa'ee Logistics, {{name}}! 👋\n\nYour account has been created. Please set your password using the link below:\n\n{{link}}\n\nThis link is valid for 24 hours. If you did not expect this message, please contact support.",
            ],
            [
                'event'         => 'password_reset_otp',
                'template_body' => "Your Sa'ee password reset code is: *{{code}}*\n\nThis code expires in 5 minutes. Do not share it with anyone.",
            ],
        ];

        foreach ($templates as $tpl) {
            WhatsAppTemplate::updateOrCreate(
                ['event'         => $tpl['event']],
                ['template_body' => $tpl['template_body']],
            );
        }
    }
}
