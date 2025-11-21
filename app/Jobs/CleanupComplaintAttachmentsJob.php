<?php

namespace App\Jobs;

use App\Repositories\Attachments\AttachmentRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupComplaintAttachmentsJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Dispatchable, SerializesModels;

    public $complaintId;
    /**
     * Create a new job instance.
     */
    public function __construct($complaintId, protected AttachmentRepository $attachmentRepository)
    {
        $this->complaintId = $complaintId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->attachmentRepository->deleteAttachmentsByComplaintId($this->complaintId);
    }
}
