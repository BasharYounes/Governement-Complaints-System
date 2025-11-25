<?php

namespace App\Http\Controllers;

use App\Events\GenericNotificationEvent;
use App\Http\Requests\UpdateComplaintStatusRequest;
use App\Repositories\Complaints\ComplaintRepository;
use App\Services\EmployeeComplaintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class EmployeeComplaintController extends Controller
{
    use ApiResponse;

    protected EmployeeComplaintService $complaintService;

    public function __construct(EmployeeComplaintService $complaintService,
        protected ComplaintRepository $complaintRepository,
    )
    {
        $this->complaintService = $complaintService;

        // Apply auth + role middleware to all routes
        $this->middleware(['auth:sanctum', 'role:employee']);
    }

    /**
     * List all complaints for the authenticated employee's government entity.
     */
    public function index()
    {
        $governmentEntityId = Auth::user()->government_entity_id;

        $complaints = $this->complaintService->listComplaints($governmentEntityId);

        return $this->success(
            'تم جلب الشكاوى بنجاح',
            $complaints
        );
    }

    /**
     * Update the status of a complaint.
     *
     * @param UpdateComplaintStatusRequest $request
     * @param int $complaintId
     */
    public function updateStatus(UpdateComplaintStatusRequest $request, int $complaintId)
    {
        $complaint = $this->complaintService->updateComplaintStatus(
            $complaintId,
            $request->status
        );

        return $this->success(
            'تم تحديث حالة الشكوى بنجاح',
            $complaint
        );
    }

    /**
     * Add notes to a complaint.
     *
     * @param Request $request
     * @param int $complaintId
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

        return $this->success(
            'تمت إضافة الملاحظات بنجاح',
            $complaint
        );
    }

    public function RequestAdditionalInformation($id,Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $complaint = $this->complaintRepository->getComplaintById($id);

        event(new GenericNotificationEvent(
            $request->user_id,
            "RequestAdditionalInformation",
            ["reference_number" => $complaint->reference_number]
        ));
    }
}
