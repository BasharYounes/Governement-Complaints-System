<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintAuditLog;
use App\Models\ComplaintAuditDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class AdminComplaintService
{
    /**
     * List all complaints with related user, attachments, and government entity.
     *
     * @return Collection
     */
    public function listAllComplaints(): Collection
    {
        return Complaint::with(['user', 'governmentEntity', 'attachments']) // use correct relationship name
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Update complaint status with audit logging.
     */
    public function updateComplaintStatus(int $complaintId, string $newStatus, ?string $notes = null): Complaint
    {
        $complaint = Complaint::findOrFail($complaintId);

        $allowedTransitions = [
            'new'         => ['in_progress', 'rejected'],
            'in_progress' => ['completed', 'rejected'],
            'completed'   => [],
            'rejected'    => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$complaint->status])) {
            throw new \Exception("Invalid status transition.");
        }

        DB::transaction(function () use ($complaint, $newStatus, $notes) {
            $auditLog = ComplaintAuditLog::create([
                'complaint_id' => $complaint->id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'description' => "Status changed from {$complaint->status} to {$newStatus}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            ComplaintAuditDetail::create([
                'audit_log_id' => $auditLog->id,
                'field_name' => 'status',
                'old_value' => $complaint->status,
                'new_value' => $newStatus,
                'notes' => $notes,
            ]);

            $complaint->status = $newStatus;
            $complaint->save();

            if ($notes) {
                $this->addComplaintNotes($complaint->id, $notes);
            }
        });

        return $complaint->fresh();
    }

    /**
     * Add notes to a complaint and log it
     */
    public function addComplaintNotes(int $complaintId, string $notes): Complaint
    {
        $complaint = Complaint::findOrFail($complaintId);

        DB::transaction(function () use ($complaint, $notes) {
            $auditLog = ComplaintAuditLog::create([
                'complaint_id' => $complaint->id,
                'user_id' => Auth::id(),
                'action' => 'note_added',
                'description' => 'Note added to complaint',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            ComplaintAuditDetail::create([
                'audit_log_id' => $auditLog->id,
                'field_name' => 'notes',
                'old_value' => null,
                'new_value' => null,
                'notes' => $notes,
            ]);
        });

        return $complaint->fresh();
    }

    /**
     * Delete a complaint and return attachments
     */
    public function deleteComplaint(int $complaintId): array
    {
        $complaint = Complaint::with('attachments')->findOrFail($complaintId);

        $attachments = $complaint->attachments->map(fn($att) => [
            'id' => $att->id,
            'file_name' => $att->file_name,
            'file_path' => $att->file_path,
            'mime_type' => $att->mime_type,
            'file_size' => $att->file_size,
            'uploaded_by' => $att->uploaded_by,
        ])->toArray();

        DB::transaction(function () use ($complaint) {
            $auditLog = ComplaintAuditLog::create([
                'complaint_id' => $complaint->id,
                'user_id' => Auth::id(),
                'action' => 'deleted',
                'description' => 'Complaint deleted by admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            ComplaintAuditDetail::create([
                'audit_log_id' => $auditLog->id,
                'field_name' => 'deleted',
                'old_value' => json_encode(['status' => $complaint->status]),
                'new_value' => null,
                'notes' => 'Complaint removed',
            ]);

            $complaint->delete();
        });

        return [
            'complaint' => $complaint,
            'attachments' => $attachments,
        ];
    }
    public function listAllEmployees()
{
    return \App\Models\User::whereNotNull('government_entity_id')
        ->with('governmentEntity')
        ->orderBy('name')
        ->get();
}
/**
 * Fetch all complaints with audit logs and details.
 *
 * @return \Illuminate\Database\Eloquent\Collection
 */
public function listAllComplaintLogs()
{
    // Load complaints with related user, government entity, attachments, audit logs and audit details
    return Complaint::with([
        'user', 
        'governmentEntity', 
        'attachments', 
        'auditLogs' => function ($query) {
            $query->with(['user', 'details'])->orderBy('created_at', 'desc');
        }
    ])->orderByDesc('created_at')->get();
}



}