<?php

namespace App\Repositories\Attachments;

use App\Models\Attachment;
use App\Services\Attachments\AttachmentService;
use Illuminate\Http\UploadedFile;


class AttachmentRepository
{
    public function __construct(protected AttachmentService $attachmentService,

    )
    {
        //
    }
    /**
     * رفع المرفق وتخزينه في قاعدة البيانات.
     */
    public function UploadAttachment(Array $attachmentRequest,$id)
    {
        $info = $this->attachmentService->extractInfoFromFile($attachmentRequest['file']);
        $info['complaint_id'] = $id;
        $info['uploaded_by'] = auth()->id();
        $info['file_path'] = $attachmentRequest['file']->storeAs('storage\app\public\attachments', $info['file_name']);
        return Attachment::create($info);
    }

    public function deleteAttachmentsByComplaintId($complaintId)
    {
        $attachments = Attachment::where('complaint_id', $complaintId)->get();

        foreach ($attachments as $attachment) {
            // حذف الملف من التخزين
            if (file_exists($attachment->file_path)) {
                unlink($attachment->file_path);
            }
            // حذف السجل من قاعدة البيانات
            $attachment->delete();
        }
    }
}
