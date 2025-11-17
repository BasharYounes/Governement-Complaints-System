<?php

namespace App\Http\Controllers;

use App\Services\AdminComplaintService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class AdminComplaintController extends Controller
{
    use ApiResponse;

    protected AdminComplaintService $complaintService;

    public function __construct(AdminComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
        // $this->middleware(['auth:sanctum', 'role:super_admin']); // only super admin
    }

    /**
     * List all complaints with full user, attachments, and government entity
     */
    public function index()
    {
        $complaints = $this->complaintService->listAllComplaints();
        return $this->success('Fetched all complaints successfully.', $complaints);
    }

    /**
     * Update status of a complaint
     */
    public function updateStatus(Request $request, int $complaintId)
    {
        $request->validate([
            'status' => 'required|in:new,in_progress,completed,rejected',
            'notes' => 'nullable|string',
        ]);

        $complaint = $this->complaintService->updateComplaintStatus(
            $complaintId,
            $request->status,
            $request->notes
        );

        return $this->success('Complaint status updated successfully.', $complaint);
    }

    /**
     * Add notes to a complaint
     */
    public function addNotes(Request $request, int $complaintId)
    {
        $request->validate([
            'notes' => 'required|string',
        ]);

        $complaint = $this->complaintService->addComplaintNotes(
            $complaintId,
            $request->notes
        );

        return $this->success('Notes added successfully.', $complaint);
    }

    /**
     * Delete a complaint
     */
    public function destroy(int $complaintId)
    {
        $result = $this->complaintService->deleteComplaint($complaintId);
        return $this->success('Complaint deleted successfully.', $result);
    }

    public function listEmployees()
{
    $employees = $this->complaintService->listAllEmployees();
    return $this->success('Fetched all employees successfully.', $employees);
}

/**
 * Fetch all audit logs with details for all complaints.
 *
 * @return \Illuminate\Database\Eloquent\Collection
 */
/**
 * Fetch all complaints with audit logs and details.
 */
public function listAllComplaintLogs()
{
    $complaints = $this->complaintService->listAllComplaintLogs();

    return $this->success(
        'Fetched all complaints with audit logs successfully.',
        $complaints
    );
}



}
