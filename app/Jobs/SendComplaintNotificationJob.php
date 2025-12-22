<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendComplaintNotificationJob implements ShouldQueue
{
    use Queueable,SerializesModels,InteractsWithQueue;

    /**
     * Create a new job instance.
     */
    public function __construct(protected $userId, protected $type, protected $data)
    {
        $this->userId = $userId;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new \App\Events\GenericNotificationEvent(
            \App\Models\User::find($this->userId),
            $this->type,
            $this->data
        ));
    }
}
