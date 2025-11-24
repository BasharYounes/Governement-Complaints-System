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
    public function UploadAttachment(Array $attachmentRequest)
    {
        $info = $this->attachmentService->extractInfoFromFile($attachmentRequest['file']);
        $info['complaint_id'] = $attachmentRequest['complaint_id'];
        $info['uploaded_by'] = auth()->id();
        // Logic to upload attachment
        $info['file_path'] = $attachmentRequest['file']->store('attachments', 'public');
        return Attachment::create($info);
    }
}
