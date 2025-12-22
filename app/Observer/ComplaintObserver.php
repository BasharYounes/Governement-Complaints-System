<?php

namespace App\Observer;


use App\Models\Complaint;
use App\Models\User;
use App\Traits\DetectsActor;
use DB;

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
        // ProcessComplaintAttachmentJob::dispatch($complaint->id,request()->file('attachments'),auth()->id());
    }

    public function updating(Complaint $complaint): void
    {
            $original = $complaint->getOriginal();
            $dirty = $complaint->getDirty();

            if (empty($dirty)) {
                return;
            }
            $action = $this->determineAction($dirty);
            $description = $this->generateDescription($action, $dirty, $original);
            $this->createAuditLog($complaint, $action, $description);
            $this->createAuditDetails($complaint, $original, $dirty);

             if ($action['Is_status_changed']) {
            dispatch(new \App\Jobs\SendComplaintNotificationJob(
                $complaint->user_id,
                'complaint_status_changed',
                [
                    'reference_number' => $complaint->referance_number,
                    'old_status' => $original['status'] ?? 'unknown',
                    'new_status' => $dirty['status'],
                ]
            ));
        }
    }

    /**
     * Handle the Complaint "updated" event.
     */
    public function updated(Complaint $complaint): void
    {
        dispatch(new \App\Jobs\SendComplaintNotificationJob(
            $complaint->user_id,
            'updateByUser',
            []
        ));
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
    \App\Jobs\CreateAuditLogJob::dispatchSync($complaint, $action, $description);
    }

    /**
     * Handle the Complaint "force deleted" event.
     */
    public function createAuditDetails(Complaint $complaint, $original, $dirty): void
    {
        dispatch(new \App\Jobs\CreateAuditDetailsJob($complaint, $original, $dirty));
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
