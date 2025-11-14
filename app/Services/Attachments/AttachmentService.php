<?php

namespace App\Services\Attachments;

use Illuminate\Http\UploadedFile;
class AttachmentService
{
    /**
     * منطق لاستخراج معلومات من الملف.
     */
    public function extractInfoFromFile(UploadedFile $file)
    {
        // منطق لاستخراج معلومات من الملف
        return [
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }
}

