<?php

namespace App\Services\Attachments;

use Illuminate\Http\UploadedFile;
class AttachmentService
{
    /**
     * منطق لاستخراج معلومات من الملف.
     */
   public function extractInfoFromFile(string $filePath)
    {
        return [
            'file_name' => basename($filePath),
            'mime_type' => mime_content_type($filePath),
            'file_size' => filesize($filePath),
        ];
    }
}

