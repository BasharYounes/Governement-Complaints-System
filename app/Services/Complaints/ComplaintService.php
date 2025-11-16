<?php
namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintAuditDetail;
use App\Models\ComplaintAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplaintService
{
    /**
     * تغيير حالة الشكوى مع إضافة ملاحظات
     */
    public function changeStatus($complaintId, $newStatus, $notes = null, $additionalData = [])
    {
        return DB::transaction(function () use ($complaintId, $newStatus, $notes, $additionalData) {
            $complaint = Complaint::where('id', $complaintId)->firstOrFail();
            if (!$complaint) {
                abort(404, 'الشكوى غير موجودة');
            }

            // استخدام request مصطنع لتمرير البيانات للـ Observer
            request()->merge([
                'notes' => $notes,
                'change_source' => $additionalData['change_source'] ?? 'manual',
                'is_automatic' => $additionalData['is_automatic'] ?? false
            ]);

            // التحديث (سي trigger الـ Observer تلقائياً)
            $complaint->update(['status' => $newStatus]);

            return [
                'complaint' => $complaint,
                'status_change' => [
                    'from' => $complaint->getOriginal('status'),
                    'to' => $newStatus,
                    'notes' => $notes
                ]
            ];
        });
    }

    /**
     * إضافة ملاحظة للشكوى
     */
    public function addNote($complaintId, $note, $isInternal = false)
    {
        return DB::transaction(function () use ($complaintId, $note, $isInternal) {

            $complaint = Complaint::find($complaintId);
            if (!$complaint) {
                abort(404, 'الشكوى غير موجودة');
            }

            // إنشاء سجل التدقيق للملاحظة
            ComplaintAuditLog::create([
                'complaint_id' => $complaintId,
                'user_id' => Auth::id(),
                'action' => 'note_added',
                'description' => $isInternal ? 'تم إضافة ملاحظة داخلية' : 'تم إضافة ملاحظة',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            return $complaint;
        });
    }

    /**
     * الحصول على سجل التدقيق الكامل للشكوى
     */
    public function getAuditTrail($complaintId, $filters = [])
    {
        $query = ComplaintAuditLog::with(['user', 'details'])
            ->where('complaint_id', $complaintId);

        // تطبيق الفلاتر إذا وجدت
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->get();

        return [
            'audit_logs' => $auditLogs,
            'summary' => $this->getAuditSummary($auditLogs)
        ];
    }

    /**
     * الحصول على ملخص سجل التدقيق
     */
    private function getAuditSummary($auditLogs)
    {
        $statusChanges = $auditLogs->where('action', 'status_changed')->count();
        $updates = $auditLogs->where('action', 'updated')->count();
        $notes = $auditLogs->where('action', 'note_added')->count();

        return [
            'total_actions' => $auditLogs->count(),
            'status_changes' => $statusChanges,
            'updates' => $updates,
            'notes_added' => $notes,
            'first_action' => $auditLogs->last()->created_at ?? null,
            'last_action' => $auditLogs->first()->created_at ?? null
        ];
    }

    /**
     * الحصول على تاريخ تغييرات الحالة فقط
     */
    public function getStatusHistory($complaintId)
    {
        $statusLogs = ComplaintAuditLog::where('complaint_id', $complaintId)
            ->where('action', 'status_changed')
            ->with(['user', 'details' => function($query) {
                $query->where('field_name', 'status');
            }])
            ->orderBy('created_at', 'asc')
            ->get();

        return $statusLogs->map(function($log) {
            $statusDetail = $log->details->firstWhere('field_name', 'status');

            return [
                'id' => $log->id,
                'user' => $log->user->name,
                'from_status' => $statusDetail->old_value_text,
                'to_status' => $statusDetail->new_value_text,
                'notes' => $statusDetail->notes,
                'date' => $log->created_at->format('Y-m-d H:i:s'),
                'ip_address' => $log->ip_address
            ];
        });
    }

    /**
     * إحصائيات تغييرات الحالة
     */
    public function getStatusStatistics($dateFrom = null, $dateTo = null)
    {
        $query = ComplaintAuditDetail::where('field_name', 'status')
            ->join('complaint_audit_logs', 'complaint_audit_details.audit_log_id', '=', 'complaint_audit_logs.id');

        if ($dateFrom) {
            $query->where('complaint_audit_logs.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('complaint_audit_logs.created_at', '<=', $dateTo);
        }

        return $query->selectRaw('new_value as status, COUNT(*) as count')
            ->groupBy('new_value')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->status => $item->count];
            });
    }
}
