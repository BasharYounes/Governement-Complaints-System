<?php

namespace App\Observer;


use App\Models\Complaint;
use App\Models\User;
use App\Traits\DetectsActor;
use DB;

class ComplaintObserver
{
    use DetectsActor;
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
        // ProcessComplaintAttachmentJob::dispatch($complaint->id,request()->file('attachments'),auth()->id());
    }

    public function updating(Complaint $complaint): void
    {
        DB::transaction(function () use ($complaint) {
            $original = $complaint->getOriginal();
            $dirty = $complaint->getDirty();

            if (empty($dirty)) {
                return;
            }
            $action = $this->determineAction($dirty);
            $description = $this->generateDescription($action, $dirty, $original);
            $auditLog = $this->createAuditLog($complaint, $action, $description);
            $this->createAuditDetails($complaint, $original, $dirty);

             if ($action['Is_status_changed']) {
            event(new \App\Events\GenericNotificationEvent(
                User::find($complaint->user_id),
                'complaint_status_changed',
                [
                    'reference_number' => $complaint->referance_number,
                    'old_status' => $original['status'] ?? 'unknown',
                    'new_status' => $dirty['status'],
                ]
            ));
        }
        });
    }

    /**
     * Handle the Complaint "updated" event.
     */
    public function updated(Complaint $complaint): void
     {
        // $original = $complaint->getOriginal();
        // $dirty = $complaint->getDirty();

        // if (empty($dirty)) {
        //     return;
        // }

        // $action = $this->determineAction($dirty);
        // $description = $this->generateDescription($action, $dirty, $original);
        // dd($action);

    }

    /**
     * Handle the Complaint "deleting" event.
     */
    public function deleting(Complaint $complaint): void
    {
        $this->createAuditLog($complaint, 'deleted', 'تم حذف الشكوى');
    }

    /**
     * Handle the Complaint "deleted" event.
     */
    public function deleted(Complaint $complaint): void
    {
        // Dispatch the job to clean up attachments
        // CleanupComplaintAttachmentsJob::dispatch($complaint->id);
    }

    /**
     * Handle the Complaint "restored" event.
     */
    public function createAuditLog(Complaint $complaint,$action,$description)
    {
        return  DB::transaction(function () use ($complaint,$action,$description) {
            $actor = $this->actor();

            $complaint->auditLogs()->create([
                'complaint_id' => $complaint->id,
                'auditable_type' => $actor ? get_class($actor) : null,
                'auditable_id' => $actor?->getKey(),
                'action' => $action['status'] ?? $action,
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
        DB::transaction(function () use ($complaint,$dirty,$original) {


            $auditLog = $complaint->auditLogs()->latest()->first();

            foreach ($dirty as $field => $newValue) {
                if ($field ==='status' && $this->request->has('notes')) {
                    $notes = $this->request->input('notes');
                }

                $originalValue = $original[$field] ?? null;

                // تحويل القيم إلى JSON فقط إذا كانت arrays أو objects
                // القيم النصية والرقمية تبقى كما هي
                $originalValue = $this->normalizeValue($originalValue);
                $normalizedValue = $this->normalizeValue($newValue);

                $auditLog->details()->create([
                    'field_name' => $field,
                    'old_value' => $originalValue,
                    'new_value' => $normalizedValue,
                    'notes' => $notes ?? null,
                ]);
            }
        });
    }

    /**
     * تحويل القيمة إلى JSON إذا كانت array أو object، وإلا إرجاعها كما هي
     */
    private function normalizeValue($value)
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        // إذا كانت JSON string، نحاول تحويلها إلى array ثم إرجاعها كـ JSON مرة أخرى
        // لضمان التنسيق الموحد
        if (is_string($value) && $this->isJson($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
        }

        return $value;
    }

    /**
     * التحقق من أن النص هو JSON صالح
     */
    private function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * تحديد نوع الإجراء بناءً على التغييرات
     */
    private function determineAction($dirty)
    {
        // إذا كان التغيير فقط في الحالة
        if (count($dirty) === 1 && array_key_exists('status', $dirty)) {
            return ['status'=>'updated','Is_status_changed'=> true];
        }

        return ['status'=>'updated','Is_status_changed'=> false];
    }

    /**
     * توليد الوصف المناسب للإجراء
     */
    private function generateDescription($action, $dirty, $original)
    {
        if ($action['Is_status_changed'] === true) {
            $oldStatus = $original['status'] ?? 'unknown';
            $newStatus = $dirty['status'];
            return "تم تغيير حالة الشكوى من {$this->getStatusText($oldStatus)} إلى {$this->getStatusText($newStatus)}";
        }
        if ($action['status'] === 'updated') {
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
