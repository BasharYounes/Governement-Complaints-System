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
    public function UploadAttachment(Array $attachmentRequest, $id)
    {
        $info = $this->attachmentService->extractInfoFromFile($attachmentRequest['file']);
        $info['complaint_id'] = $id;
        $info['uploaded_by'] = auth()->guard('api')->id();
        // Logic to upload attachment
        $info['file_path'] = $attachmentRequest['file']->store('attachments', 'public');
        return Attachment::create($info);
    }

    public function getAttachmentById($id): Attachment
    {
        $attachment = Attachment::find($id);
        if (! $attachment) {
            abort(response()->json([
                'success' => false,
                'message' => 'حدث خطأ غير متوقع',
                'errors' => 'المرفق غير موجود'
            ], 404));
        }

        return $attachment;
    }
}
