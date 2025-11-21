<?php

namespace App\Jobs;

use App\Repositories\Attachments\AttachmentRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessComplaintAttachmentJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, Dispatchable;

    public $complaintId;
    public $attachment;

    /**
     * Create a new job instance.
     */
    public function __construct($complaintId, $attachment, protected AttachmentRepository $attachmentRepository)
    {
        $this->complaintId = $complaintId;
        $this->attachment = $attachment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->attachmentRepository->UploadAttachment(['file' => $this->attachment], $this->complaintId);
    }
}
