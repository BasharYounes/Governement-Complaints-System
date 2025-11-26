<?php

namespace App\Http\Controllers;

use App\Repositories\Attachments\AttachmentRepository;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{

    public function __construct(protected AttachmentRepository $attachmentRepository)
    {
        //
    }

    public function show($id)
    {
        $attachment = $this->attachmentRepository->getAttachmentById($id);

        if (! Storage::exists($attachment->file_path)) {
            return $this->error('File not found in storage.', 404);
        }

        return Storage::response($attachment->file_path);
    }
}
