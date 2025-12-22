<?php

namespace App\Jobs;

use App\Models\Attachment;
use App\Repositories\Attachments\AttachmentRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessComplaintAttachmentJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Dispatchable;

    public $complaintId;
    public $path;

    public $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($complaintId, $path, $userId)
    {
        $this->complaintId = $complaintId;
        $this->path = $path;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // $attachmentRepository->UploadAttachment($this->path, $this->complaintId , $this->userId);
        $fileFullPath = Storage::path($this->path);
        $info = $this->extractInfoFromFile($fileFullPath);
        $info['complaint_id'] = $this->complaintId;
        $info['uploaded_by'] = $this->userId;
        $info['file_path'] = $this->path;
       Attachment::create($info);
    }

    public function extractInfoFromFile(string $filePath)
    {
        return [
            'file_name' => basename($filePath),
            'mime_type' => mime_content_type($filePath),
            'file_size' => filesize($filePath),
        ];
    }
}
