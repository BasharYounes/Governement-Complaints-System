<?php

namespace App\Repositories\Attachments;

use App\Models\Attachment;
use App\Services\Attachments\AttachmentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttachmentRepository
{
    public function __construct(protected AttachmentService $attachmentService,

    )
    {
        //
    }
    public function UploadAttachment($path, $id): Attachment
    {
        $fileFullPath = Storage::path($path);
        $info = $this->attachmentService->extractInfoFromFile($fileFullPath);
        $info['complaint_id'] = $id;
        $info['uploaded_by'] = auth()->user()->id;
        $info['file_path'] = $path;
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
