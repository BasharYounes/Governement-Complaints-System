<?php

namespace App\Services;

use App\Aspects\TransactionAspect;
use App\Models\Complaint;
use App\Models\ComplaintAuditLog;
use App\Models\ComplaintAuditDetail;
use App\Models\Attachment;
use App\Repositories\Complaints\ComplaintRepository;
use App\Repositories\Web\EmployeeRepository;
use App\Traits\ApiResponse;
use Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class EmployeeComplaintService
{
    use ApiResponse;
    protected EmployeeRepository $EmployeeRepo;
    public function __construct(protected ComplaintRepository $complaintRepository, EmployeeRepository $EmployeeRepo)
    {
        $this->EmployeeRepo = $EmployeeRepo;
    }
    /**
     * Allowed status transitions for complaints.
     */
    protected array $allowedTransitions = [
        'new'         => ['in_progress', 'rejected'],
        'in_progress' => ['completed', 'rejected'],
        'completed'   => [], // final
        'rejected'    => [], // final
    ];

    /**
     * List complaints for a government entity.
     *
     * @param int $entityId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listComplaints(int $entityId)
    {
        return Complaint::where('government_entity_id', $entityId)
            ->with(['attachments'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getGroupedComplaints(int $entityId): array
    {
        return $this->EmployeeRepo->getGrouped($entityId);
    }

    /**
     * Update complaint status with validation and audit logging
     *
     * @param int $complaintId
     * @param array $newStatus
     * @param string|null $notes
     * @return Complaint
     */
   public function updateComplaintStatus(int $complaintId, array $data, ?string $notes = null)
{
    $employee = auth()->user();

    $complaint = Complaint::where('id', $complaintId)
                          ->where('government_entity_id', $employee->government_entity_id)
                          ->first();

        if (!$complaint) {
            throw new ModelNotFoundException("Complaint not found or you don't have access.");
        }
        $currentStatus = $complaint->status;
        $newStatus = $data['status'];

        // Check allowed transitions
        if (!in_array($newStatus, $this->allowedTransitions[$currentStatus])) {
            throw new \Exception("الحالة المرسلة غير صالحة.");
        }

        $newComplaint = TransactionAspect::handle(
            $this->complaintRepository,
            'updateComplaint',
            [$complaintId, ['status' => $newStatus]]
        );

    $currentStatus = $complaint->status;

    //  Check if currentStatus exists in allowedTransitions
    if (!array_key_exists($currentStatus, $this->allowedTransitions)) {
        throw new \Exception("الحالة الحالية غير معروفة في النظام.");
    }

    //  Check if newStatus is allowed for this currentStatus
    if (!in_array($newStatus, $this->allowedTransitions[$currentStatus])) {
        throw new \Exception("الحالة المرسلة غير صالحة.");
    }

    $newComplaint = $this->complaintRepository->updateComplaint($complaintId, [
        'status' => $newStatus,
        'notes'  => $notes
    ]);

    return $newComplaint;
}



    /**
     * Add notes to a complaint and log the change
     *
     * @param int $complaintId
     * @param string $notes
     * @param Request|null $request
     * @return Complaint
     */
    public function addComplaintNotes(int $complaintId, string $notes, ?Request $request = null): Complaint
    {
        return DB::transaction(function () use ($complaintId, $notes, $request) {
            $employee = Auth::user();

            $complaint = Complaint::findOrFail($complaintId);

            if ($complaint->government_entity_id !== $employee->government_entity_id) {
                abort(403, 'You are not allowed to modify complaints outside your entity.');
            }

            // Audit log for note addition
            $auditLog = ComplaintAuditLog::create([
                'complaint_id' => $complaint->id,
                'user_id'      => $employee->id,
                'action'       => 'note_added',
                'description'  => "Note added to complaint",
                'ip_address'   => $request?->ip() ?? request()->ip(),
                'user_agent'   => $request?->header('User-Agent') ?? request()->header('User-Agent'),
            ]);

            ComplaintAuditDetail::create([
                'audit_log_id' => $auditLog->id,
                'field_name'   => 'notes',
                'old_value'    => null,
                'new_value'    => null,
                'notes'        => $notes,
            ]);

            return $complaint->fresh();
        });
    }

    /**
     * Delete a complaint and return its attachments metadata.
     *
     * @param int $complaintId
     * @param Request|null $request
     * @return array
     */
    public function deleteComplaint(int $complaintId, ?Request $request = null): array
    {
        return DB::transaction(function () use ($complaintId, $request) {
            $employee = Auth::user();

            $complaint = Complaint::with('attachments')->findOrFail($complaintId);

            if ($complaint->government_entity_id !== $employee->government_entity_id) {
                abort(403, 'You are not allowed to delete complaints outside your entity.');
            }

            $attachments = $complaint->attachments->map(fn($att) => [
                'id' => $att->id,
                'file_name' => $att->file_name,
                'file_path' => $att->file_path,
                'mime_type' => $att->mime_type,
                'file_size' => $att->file_size,
                'uploaded_by' => $att->uploaded_by,
            ])->toArray();

            // Audit log for deletion
            $auditLog = ComplaintAuditLog::create([
                'complaint_id' => $complaint->id,
                'user_id'      => $employee->id,
                'action'       => 'deleted',
                'description'  => 'Complaint deleted by employee',
                'ip_address'   => $request?->ip() ?? request()->ip(),
                'user_agent'   => $request?->header('User-Agent') ?? request()->header('User-Agent'),
            ]);

            ComplaintAuditDetail::create([
                'audit_log_id' => $auditLog->id,
                'field_name'   => 'deleted',
                'old_value'    => json_encode(['status' => $complaint->status]),
                'new_value'    => null,
                'notes'        => 'Complaint removed',
            ]);

            $complaint->delete();

            return [
                'complaint'   => $complaint,
                'attachments' => $attachments,
            ];
        });
    }


    public function searchForEmployee(?string $keyword)
{
    $employee = auth('employee-api')->user();
    return $this->EmployeeRepo->searchForEmployee($keyword, $employee->government_entity_id);
}
}
