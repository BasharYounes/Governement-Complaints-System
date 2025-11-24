<?php

namespace Database\Seeders;

use App\Models\Complaint;
use Illuminate\Database\Seeder;

class ComplaintsSeeder extends Seeder
{
    public function run(): void
    {
        $complaints = [
            [
                'user_id' => 3, 
                'description' => 'تم انقطاع المياه منذ 24 ساعة ولم يتم التزويد بعد.',
                'government_entity_id' => 1,
                'status' => 'new',
                'type' => 'خدمة عامة',
                'location' => 'دمشق - مركز المدينة',
                'reference_number' => 'REF-001',
            ],
            [
                'user_id' => 3,
                'description' => 'الطلب تم تقديمه منذ أسبوع بدون تحديث.',
                'government_entity_id' => 2,
                'status' => 'in_progress',
                'type' => 'خدمة إدارية',
                'location' => 'دمشق - الزبلطاني',
                'reference_number' => 'REF-002',
            ],
            [
                'user_id' => 3,
                'description' => 'هناك رسوم إضافية لم يتم توضيحها.',
                'government_entity_id' => 3,
                'status' => 'completed',
                'type' => 'فاتورة صحية',
                'location' => 'حلب - الحمراء',
                'reference_number' => 'REF-003',
            ],
            [
                'user_id' => 3,
                'description' => 'عدم التزام بعض المدارس بمواعيد الدوام الرسمية.',
                'government_entity_id' => 4,
                'status' => 'rejected',
                'type' => 'تعليم',
                'location' => 'حمص - المدينة القديمة',
                'reference_number' => 'REF-004',
            ],
        ];

        Complaint::insert($complaints);
    }
}
