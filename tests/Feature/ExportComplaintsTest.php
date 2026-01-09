<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ExportComplaintsTest extends TestCase
{
    /**
     * اختبار تصدير CSV للشهر الكامل
     */
    public function test_monthly_csv_export_full_month()
    {
        // إنشاء مستخدم اختبار
        $user = User::factory()->create();

        // إنشاء شكاوى للشهر الحالي
        Complaint::factory()->count(5)->create([
            'created_at' => now(),
        ]);

        // طلب التصدير
        $response = $this->actingAs($user)
            ->getJson('/api/reports/monthly/csv?month=' . now()->format('Y-m'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data' => ['url']]);
    }

    /**
     * اختبار تصدير CSV خلال نطاق زمني
     */
    public function test_monthly_csv_export_date_range()
    {
        // إنشاء مستخدم اختبار
        $user = User::factory()->create();

        // إنشاء شكاوى في نطاق زمني معين
        $fromDate = '2026-01-01';
        $toDate = '2026-01-15';

        Complaint::factory()->count(3)->create([
            'created_at' => Carbon::parse($fromDate)->addDays(5),
        ]);

        // طلب التصدير بنطاق زمني
        $response = $this->actingAs($user)
            ->getJson("/api/reports/monthly/csv?from_date={$fromDate}&to_date={$toDate}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'message', 'data' => ['url']]);
    }

    /**
     * اختبار تصدير PDF للشهر الكامل
     */
    public function test_monthly_pdf_export_full_month()
    {
        // إنشاء مستخدم اختبار
        $user = User::factory()->create();

        // إنشاء شكاوى للشهر الحالي
        Complaint::factory()->count(5)->create([
            'created_at' => now(),
        ]);

        // طلب التصدير
        $response = $this->actingAs($user)
            ->getJson('/api/reports/monthly/pdf?month=' . now()->format('Y-m'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'url']);
    }

    /**
     * اختبار تصدير PDF خلال نطاق زمني
     */
    public function test_monthly_pdf_export_date_range()
    {
        // إنشاء مستخدم اختبار
        $user = User::factory()->create();

        // إنشاء شكاوى في نطاق زمني معين
        $fromDate = '2026-01-01';
        $toDate = '2026-01-15';

        Complaint::factory()->count(3)->create([
            'created_at' => Carbon::parse($fromDate)->addDays(5),
        ]);

        // طلب التصدير بنطاق زمني
        $response = $this->actingAs($user)
            ->getJson("/api/reports/monthly/pdf?from_date={$fromDate}&to_date={$toDate}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'url']);
    }

    /**
     * اختبار التحقق من صيغة التاريخ
     */
    public function test_invalid_date_format()
    {
        $user = User::factory()->create();

        // طلب بصيغة تاريخ غير صحيحة
        $response = $this->actingAs($user)
            ->getJson('/api/reports/monthly/csv?from_date=01-01-2026&to_date=15-01-2026');

        $response->assertStatus(422);
    }

    /**
     * اختبار التحقق من أن تاريخ النهاية بعد البداية
     */
    public function test_end_date_before_start_date()
    {
        $user = User::factory()->create();

        // طلب برجاء من النهاية قبل البداية
        $response = $this->actingAs($user)
            ->getJson('/api/reports/monthly/csv?from_date=2026-01-15&to_date=2026-01-01');

        $response->assertStatus(422);
    }
}
