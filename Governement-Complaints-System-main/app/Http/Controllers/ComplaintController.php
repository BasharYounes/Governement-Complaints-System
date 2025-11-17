<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachments\AttachmentRequest;
use App\Models\Complaint;
use App\Repositories\Attachments\AttachmentRepository;
use App\Services\Attachments\AttachmentService;
use Illuminate\Http\Request;
use App\Http\Requests\Complaints\ComplaintRequest;

class ComplaintController extends Controller
{
    public function __construct(protected AttachmentService $attachmentService,
    public AttachmentRepository $attachmentRepository
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
       $attachments = $this->attachmentRepository->UploadAttachment($attachmentRequest->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Complaint $complaint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Complaint $complaint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Complaint $complaint)
    {
        //
    }
}
