<?php

namespace App\Services\Attachments;

use Illuminate\Http\UploadedFile;
class AttachmentService
{
    /**
     * منطق لاستخراج معلومات من الملف.
     */
   public function extractInfoFromFile(UploadedFile $uploadedFile)
    {
        return [
            'file_name' => $uploadedFile->getClientOriginalName() ,
            'mime_type' => $uploadedFile->getClientMimeType(),
            'file_size' => $uploadedFile->getSize(),
        ];
    }
}

