<?php

namespace App\Listeners;

use App\Events\UserRegister;
use App\Events\UserRegistered;
use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendVerificationEmail 
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
   public function handle(UserRegistered $event)
{
    try {
        Mail::to($event->user->email)->send(
            new VerificationMail($event->code)
        );
    } catch (\Exception $e) {
        \Log::error("Email failed: " . $e->getMessage());
    }
}
}
