<?php

namespace App\Http\Controllers;

use App\Events\GenericNotificationEvent;
use App\Http\Requests\UpdateComplaintStatusRequest;
use App\Repositories\ComplaintEmployeeRepository;
use App\Repositories\Complaints\ComplaintRepository;
use App\Services\EmployeeComplaintService;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;


class EmployeeComplaintController extends Controller
{

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
        $employee = auth()->user();
        $lockKey = 'complaint_update_'.$complaintId;

        $lockOwner = Cache::get($lockKey);

        if ($lockOwner && $lockOwner !== $employee->id) {
            return $this->error('The time allowed for editing has expired Or This complaint is currently being edited by another user.',null,403);
        }


        $complaint = $this->complaintService->updateComplaintStatus(
            $complaintId,
            $request->validated('status'),
           $request->validated('notes') ?? null
        );

        Cache::forget($lockKey);


        return $this->success(
            'تم تحديث حالة الشكوى بنجاح',
            $complaint,
            200
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
    public function getAllComplaint()
    {
        return $this->success('All complaints retrieved successfully',
         $this->complaintRepository->allComplaint(),
         200);
    }

    public function show($id)
    {
        $complaint = $this->complaintRepository->getComplaintById($id);
        return $this->success('Complaint retrieved successfully', $complaint, 200);
    }
}
