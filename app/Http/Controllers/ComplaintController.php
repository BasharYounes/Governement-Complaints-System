<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachments\AttachmentRequest;
use App\Http\Requests\Complaints\ComplaintUpdateRequest;
use App\Repositories\Attachments\AttachmentRepository;
use App\Repositories\Complaints\ComplaintRepository;
use App\Repositories\GovernementEntities\GovernmentEntityRepository;
use App\Repositories\ReferanceNumberRepository\ReferanceNumberRepository;
use App\Services\Attachments\AttachmentService;
use App\Services\EmployeeComplaintService;
use App\Traits\ApiResponse;
use App\Http\Requests\Complaints\ComplaintRequest;
use Cache;

class ComplaintController extends Controller
{
    // Using the ApiResponse trait for standardized API responses
    use ApiResponse;
    /**
     * Constructor to initialize repositories and services.
     */
    public function __construct(protected AttachmentService $attachmentService,
    protected AttachmentRepository $attachmentRepository,
    protected ReferanceNumberRepository $referenceNumberRepository,
    protected GovernmentEntityRepository $governmentEntityRepository,
    protected ComplaintRepository $complaintRepository,
    protected EmployeeComplaintService $complaintService
    )
    {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(ComplaintRequest $complaintRequest,AttachmentRequest $attachmentRequest)
    {
        $governmentEntity = $this->governmentEntityRepository->getCodeById($complaintRequest->government_entity_id);

        $referenceNumber = $this->referenceNumberRepository->generateReferenceNumber($governmentEntity->code);

        $complaint = $this->complaintRepository->createComplaint($complaintRequest->validated(), $referenceNumber);

        $attachments = $this->attachmentRepository->UploadAttachment($attachmentRequest->validated(), $complaint->id);

    return $this->success('Complaint created successfully',[$complaint,$attachments], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $complaint = $this->complaintRepository->getComplaintById($id);
    return $this->success('Complaint retrieved successfully', $complaint, 200);
    }
    /**
     * Check if the complaint is being edited by another employee.
     */
    public function edit($id)
    {
        $User = auth()->user();
        $lockKey = 'complaint_update_'.$id;

        $lockOwner = Cache::get($lockKey);

        if ($lockOwner && $lockOwner !== $User->id) {
            return $this->error('This complaint is currently being edited by another employee.',null, 403);
        }

        Cache::put($lockKey, $User->id, now()->addMinutes(10));

        $complaint = $this->complaintRepository->getComplaintById($id);

    return $this->success('Complaint allowed editing', $complaint, 200);
    }
    /**
     * Update the specified resource in storage after checking for edit locks.
     */
    public function update(ComplaintUpdateRequest $request,$id)
    {
        $complaint = $this->complaintRepository->getComplaintById($id);
        $updatedComplaint = $this->complaintRepository->updateComplaint($id,$request->validated());

    return $this->success('Complaint updated successfully', $updatedComplaint, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $this->complaintRepository->deleteComplaint($id);
    return $this->success('Complaint deleted successfully', null, 200);
    }

    public function addAttachment(AttachmentRequest $attachmentRequest,$id)
    {
        $attachments = $this->attachmentRepository->UploadAttachment($attachmentRequest->validated(),$id);
    return $this->success('Attachments uploaded successfully', $attachments, 201);
    }

    public function getComplaintsforUser()
    {
        $complaints = $this->complaintRepository->getComplaintsByUser();
    return $this->success('User complaints retrieved successfully', $complaints, 200);
    }
}
