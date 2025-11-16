<?php

namespace App\Observer;

use App\Models\Complaint;

class ComplaintObserver
{
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }
    /**
     * Handle the Complaint "created" event.
     */
    public function created(Complaint $complaint): void
    {
        $this->createAuditLog($complaint,'created','تم إنشاء الشكوى');
    }
        // Intentionally left empty. Audit log for deletion is created in the "deleting" event
        // to ensure the complaint record still exists when the audit row is inserted.
    public function updating(Complaint $complaint): void
    {
        $original = $complaint->getOriginal();
        $dirty = $complaint->getDirty();
        // إذا لم تتغير أي بيانات، توقف
        if (empty($dirty)) {
            return;
        }
        // تحديد نوع الإجراء ووصفه
        $action = $this->determineAction($dirty);
        //إنشاء وصف مناسب للإجراء
        $description = $this->generateDescription($action, $dirty, $original);
        // إنشاء سجل التدقيق
        $auditLog = $this->createAuditLog($complaint, $action, $description);
        // تسجيل التفاصيل المتغيرة
        $this->createAuditDetails($complaint, $original, $dirty);
    }

    /**
     * Handle the Complaint "updated" event.
     */
    public function updated(Complaint $complaint): void
     {
    //     $original = $complaint->getOriginal();
    //     $dirty = $complaint->getDirty();
    //     // إذا لم تتغير أي بيانات، توقف
    //     if (empty($dirty)) {
    //         return;
    //     }
    //     // تحديد نوع الإجراء ووصفه
    //     $action = $this->determineAction($dirty);
    //     //إنشاء وصف مناسب للإجراء
    //     $description = $this->generateDescription($action, $dirty, $original);
    //     if ($action ==='status_changed') {
    //         event(new \App\Events\GenericNotificationEvent(
    //             $complaint->user,
    //             'complaint_status_changed',
    //             [
    //                 'reference_number' => $complaint->referance_number,
    //                 'old_status' => $original['status'] ?? 'unknown',
    //                 'new_status' => $dirty['status'],
    //             ]
    //         ));
    //     }
    }

    /**
     * Handle the Complaint "deleting" event.
     */
    public function deleting(Complaint $complaint): void
    {
        $this->createAuditLog($complaint, 'deleted', 'تم حذف الشكوى');
    }

    /**
     * Handle the Complaint "restored" event.
     */
    public function createAuditLog(Complaint $complaint,$action,$description)
    {
        return  \DB::transaction(function () use ($complaint,$action,$description) {
            $complaint->auditLogs()->create([
                'complaint_id' => $complaint->id,
                'user_id' => auth()->id(),
                'action' => $action,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]);
        });


    }

    /**
     * Handle the Complaint "force deleted" event.
     */
    public function createAuditDetails(Complaint $complaint, $original, $dirty): void
    {
        \DB::transaction(function () use ($complaint,$dirty,$original) {


            $auditLog = $complaint->auditLogs()->latest()->first();

            foreach ($dirty as $field => $newValue) {
                if ($field ==='status' && $this->request->has('notes')) {
                    $notes = $this->request->input('notes');
                }

                $originalValue = $original[$field] ?? null;

                $auditLog->details()->create([
                    'field_name' => $field,
                    'old_value' => $originalValue,
                    'new_value' => $newValue,
                    'notes' => $notes ?? null,
                ]);
            }
        });
    }

    /**
     * تحديد نوع الإجراء بناءً على التغييرات
     */
    private function determineAction($dirty)
    {
        // إذا كان التغيير فقط في الحالة
        if (count($dirty) === 1 && array_key_exists('status', $dirty)) {
            return 'status_changed';
        }

        return 'updated';
    }

    /**
     * توليد الوصف المناسب للإجراء
     */
    private function generateDescription($action, $dirty, $original)
    {
        if ($action === 'status_changed') {
            $oldStatus = $original['status'] ?? 'unknown';
            $newStatus = $dirty['status'];
            return "تم تغيير حالة الشكوى من {$this->getStatusText($oldStatus)} إلى {$this->getStatusText($newStatus)}";
        }

        if ($action === 'updated') {
            $changedFields = array_keys($dirty);
            if (count($changedFields) === 1) {
                return "تم تحديث حقل {$this->getFieldLabel($changedFields[0])}";
            }
            return "تم تحديث " . count($changedFields) . " حقول";
        }

        return 'تم إجراء تغيير على الشكوى';
    }

    /**
     * الحصول على نص الحالة
     */
    private function getStatusText($status)
    {
        $statuses = [
            'new' => 'جديدة',
            'in_progress' => 'قيد المعالجة',
            'resolved' => 'تم الحل',
            'rejected' => 'مرفوضة'
        ];

        return $statuses[$status] ?? $status;
    }

    /**
     * الحصول على تسمية الحقل
     */
    private function getFieldLabel($field)
    {
        $labels = [
            'type' => 'العنوان',
            'description' => 'الوصف',
            'government_entity_id' => 'الجهة الحكومية'
        ];

        return $labels[$field] ?? $field;
    }
}
