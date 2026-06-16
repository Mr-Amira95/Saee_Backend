<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JordanCitiesSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            ['name' => 'Amman',   'name_ar' => 'عمان',    'areas' => [
                ['name' => 'Downtown (Al-Balad)',   'name_ar' => 'وسط البلد'],
                ['name' => 'Abdali',                'name_ar' => 'العبدلي'],
                ['name' => 'Shmeisani',             'name_ar' => 'الشميساني'],
                ['name' => 'Jabal Amman',           'name_ar' => 'جبل عمان'],
                ['name' => 'Sweifieh',              'name_ar' => 'الصويفية'],
                ['name' => 'Khalda',                'name_ar' => 'خلدا'],
                ['name' => 'Um Uthaina',            'name_ar' => 'أم أوثينا'],
                ['name' => 'Tlaa Al-Ali',           'name_ar' => 'تلاع العلي'],
                ['name' => 'Jubeiha',               'name_ar' => 'الجبيهة'],
                ['name' => 'University District',   'name_ar' => 'منطقة الجامعة'],
                ['name' => 'Marka',                 'name_ar' => 'ماركا'],
                ['name' => 'Tabarbour',             'name_ar' => 'طبربور'],
                ['name' => 'Abu Nsair',             'name_ar' => 'أبو نصير'],
                ['name' => 'Rabieh',                'name_ar' => 'الرابية'],
                ['name' => 'Airport Road',          'name_ar' => 'طريق المطار'],
                ['name' => 'Zarqa Road',            'name_ar' => 'طريق الزرقاء'],
                ['name' => 'Al-Nuzha',              'name_ar' => 'النزهة'],
                ['name' => 'Medina Street',         'name_ar' => 'شارع المدينة المنورة'],
                ['name' => 'Gardens',               'name_ar' => 'الجاردنز'],
                ['name' => 'Dahiyat Al-Rashid',     'name_ar' => 'ضاحية الراشد'],
            ]],
            ['name' => 'Zarqa',   'name_ar' => 'الزرقاء', 'areas' => [
                ['name' => 'Zarqa City Center',     'name_ar' => 'مركز مدينة الزرقاء'],
                ['name' => 'New Zarqa',             'name_ar' => 'الزرقاء الجديدة'],
                ['name' => 'Russeifa',              'name_ar' => 'الرصيفة'],
                ['name' => 'Hashimiyya',            'name_ar' => 'الهاشمية'],
                ['name' => 'Azraq',                 'name_ar' => 'الأزرق'],
                ['name' => 'Al-Dhuleil',            'name_ar' => 'الضليل'],
                ['name' => 'Hallabat',              'name_ar' => 'الحلابات'],
            ]],
            ['name' => 'Irbid',   'name_ar' => 'إربد',    'areas' => [
                ['name' => 'Irbid City Center',     'name_ar' => 'مركز مدينة إربد'],
                ['name' => 'Downtown Irbid',        'name_ar' => 'وسط إربد'],
                ['name' => 'University District',   'name_ar' => 'منطقة الجامعة'],
                ['name' => 'Husn',                  'name_ar' => 'الحصن'],
                ['name' => 'Ramtha',                'name_ar' => 'الرمثا'],
                ['name' => 'Bani Kinanah',          'name_ar' => 'بني كنانة'],
                ['name' => 'Koura',                 'name_ar' => 'الكورة'],
                ['name' => 'Aydoun',                'name_ar' => 'عيدون'],
            ]],
            ['name' => 'Aqaba',   'name_ar' => 'العقبة',  'areas' => [
                ['name' => 'Aqaba City Center',     'name_ar' => 'مركز مدينة العقبة'],
                ['name' => 'Tourist Area',          'name_ar' => 'المنطقة السياحية'],
                ['name' => 'Port Area',             'name_ar' => 'منطقة الميناء'],
                ['name' => 'Industrial Zone',       'name_ar' => 'المنطقة الصناعية'],
                ['name' => 'Al-Shaikh Hussain',     'name_ar' => 'الشيخ حسين'],
                ['name' => 'ASEZA Zone',            'name_ar' => 'منطقة العقبة الاقتصادية'],
            ]],
            ['name' => 'Madaba',  'name_ar' => 'مادبا',   'areas' => [
                ['name' => 'Madaba City Center',    'name_ar' => 'مركز مدينة مادبا'],
                ['name' => 'Dhiban',                'name_ar' => 'ذيبان'],
                ['name' => 'Libb',                  'name_ar' => 'لبن'],
                ['name' => 'Naur',                  'name_ar' => 'ناعور'],
            ]],
            ['name' => 'Karak',   'name_ar' => 'الكرك',   'areas' => [
                ['name' => 'Karak City Center',     'name_ar' => 'مركز مدينة الكرك'],
                ['name' => 'Qatraneh',              'name_ar' => 'القطرانة'],
                ['name' => 'Mazar',                 'name_ar' => 'المزار'],
                ['name' => 'Al-Mutah',              'name_ar' => 'مؤتة'],
            ]],
            ['name' => 'Balqa (Salt)', 'name_ar' => 'البلقاء (السلط)', 'areas' => [
                ['name' => 'Salt City Center',      'name_ar' => 'مركز مدينة السلط'],
                ['name' => 'Fuheis',                'name_ar' => 'الفحيص'],
                ['name' => 'Mahis',                 'name_ar' => 'ماحص'],
                ['name' => 'Deir Alla',             'name_ar' => 'دير علا'],
                ['name' => 'Shouneh Al-Janubiyyeh','name_ar' => 'الشونة الجنوبية'],
            ]],
            ['name' => 'Mafraq',  'name_ar' => 'المفرق',  'areas' => [
                ['name' => 'Mafraq City Center',    'name_ar' => 'مركز مدينة المفرق'],
                ['name' => 'Badia',                 'name_ar' => 'البادية'],
                ['name' => 'Safawi',                'name_ar' => 'الصفاوي'],
                ['name' => 'Ruweished',             'name_ar' => 'الرويشد'],
            ]],
            ['name' => 'Jerash',  'name_ar' => 'جرش',     'areas' => [
                ['name' => 'Jerash City Center',    'name_ar' => 'مركز مدينة جرش'],
                ['name' => 'Sakeb',                 'name_ar' => 'ساكب'],
                ['name' => 'Burma',                 'name_ar' => 'برما'],
            ]],
            ['name' => 'Ajloun',  'name_ar' => 'عجلون',   'areas' => [
                ['name' => 'Ajloun City Center',    'name_ar' => 'مركز مدينة عجلون'],
                ['name' => 'Kofranjeh',             'name_ar' => 'كفرنجة'],
                ['name' => 'Anjara',                'name_ar' => 'عنجرة'],
            ]],
            ['name' => 'Tafilah', 'name_ar' => 'الطفيلة', 'areas' => [
                ['name' => 'Tafilah City Center',   'name_ar' => 'مركز مدينة الطفيلة'],
                ['name' => 'Buseira',               'name_ar' => 'بصيرا'],
                ['name' => 'Sela',                  'name_ar' => 'السلع'],
            ]],
            ['name' => "Ma'an",   'name_ar' => 'معان',    'areas' => [
                ['name' => "Ma'an City Center",     'name_ar' => 'مركز مدينة معان'],
                ['name' => 'Petra',                 'name_ar' => 'البتراء'],
                ['name' => 'Wadi Musa',             'name_ar' => 'وادي موسى'],
                ['name' => 'Shoubak',               'name_ar' => 'الشوبك'],
                ['name' => 'Humaimah',              'name_ar' => 'حميمة'],
            ]],
        ];

        foreach ($cities as $cityData) {
            $areas = $cityData['areas'];
            unset($cityData['areas']);
            $cityData['country_code'] = 'JO';
            $cityData['created_at']   = now();
            $cityData['updated_at']   = now();

            $cityId = DB::table('cities')->insertGetId($cityData);

            $areaRows = array_map(fn($a) => array_merge($a, [
                'city_id'    => $cityId,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]), $areas);

            DB::table('areas')->insert($areaRows);
        }
    }
}
