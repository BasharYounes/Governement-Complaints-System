<?php

namespace App\Jobs;

use App\Models\Complaint;
use App\Traits\DetectsActor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateAuditLogJob implements ShouldQueue
{
    use DetectsActor,Queueable,SerializesModels,InteractsWithQueue;

    protected $complaint;
    protected $action;
    protected $description;

    /**
     * Create a new job instance.
     */
    public function __construct($complaint, $action, $description)
    {
        $this->complaint = $complaint;
        $this->action = $action;
        $this->description = $description;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $actor = $this->actor();

        $this->complaint->auditLogs()->create([
            'complaint_id' => $this->complaint->id,
            'auditable_type' => $actor ? get_class($actor) : null,
            'auditable_id' => $actor?->getKey(),
            'action' => $this->action['status'] ?? $this->action,
            'description' => $this->description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
        ]);
    }
}
