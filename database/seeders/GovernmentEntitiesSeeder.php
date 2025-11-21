<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GovernmentEntitiesSeeder extends Seeder
{
    public function run()
    {
        $entities = [
            [
                'name' => 'وزارة الداخلية',
                'code' => 'MOI',
                'location' => 'دمشق - ساحة المرجة',
            ],
            [
                'name' => 'وزارة الصحة',
                'code' => 'MOH',
                'location' => 'دمشق - المزة',
            ],
            [
                'name' => 'وزارة التربية',
                'code' => 'EDU',
                'location' => 'دمشق - الروضة',
            ],
            [
                'name' => 'وزارة الكهرباء',
                'code' => 'ELC',
                'location' => 'دمشق - العدوي',
            ],
            [
                'name' => 'وزارة الموارد المائية',
                'code' => 'MW',
                'location' => 'دمشق - الفحامة',
            ],
            [
                'name' => 'وزارة الإدارة المحلية والبيئة',
                'code' => 'MOLA',
                'location' => 'دمشق - أبورمانة',
            ],
            [
                'name' => 'وزارة الاتصالات والتقانة',
                'code' => 'ICT',
                'location' => 'دمشق - كفرسوسة',
            ],
            [
                'name' => 'وزارة الشؤون الاجتماعية والعمل',
                'code' => 'MSA',
                'location' => 'دمشق - مشروع دمر',
            ],
            [
                'name' => 'البلدية المركزية',
                'code' => 'MUN',
                'location' => 'دمشق - شارع الثورة',
            ],
            [
                'name' => 'الشركة العامة للكهرباء',
                'code' => 'ELEC',
                'location' => 'دمشق - باب مصلى',
            ],
        ];

        DB::table('government_entities')->insert($entities);
    }
}
