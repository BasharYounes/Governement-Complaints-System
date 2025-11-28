<?php

namespace App\Listeners;

use App\Events\GenericNotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Notifications\NotificationService;

class HandleGenericNotification
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
    public function handle(GenericNotificationEvent $event): void
    {
       $notificationService = new NotificationService();
       $notificationService->send($event->user, $event->type, $event->data);
    }
}
