<?php

namespace App\Http\Controllers;

use App\Repositories\Attachments\AttachmentRepository;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApiResponse;
class AttachmentController extends Controller
{
    public function __construct(protected AttachmentRepository $attachmentRepository)
    {
        //
    }

    public function show($id)
    {
        $attachment = $this->attachmentRepository->getAttachmentById($id);

        // files are stored under the `public` disk (storage/app/public)
        if (!Storage::disk('public')->exists($attachment->file_path)) {
            return $this->error('File not found in storage.', null, 404);
        }

        $path = Storage::disk('public')->path($attachment->file_path);

        return response()->file($path);
    }
}
