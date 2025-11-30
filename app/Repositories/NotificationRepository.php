<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Notification;

class NotificationRepository
{
    public function MarkAsReadAllNotification(User $user)
    {
        $user->notifications()
        ->where('is_read', false)
        ->update(['is_read' => true]);
    }

    public function findNotification($id, User $user)
    {
        return Notification::where('id', $id)
        ->where('user_id', $user->id)
        ->firstOrFail();
    }

    public function MarkAsReadNotification(Notification $notification)
    {
        $notification->update(['is_read' => true]);
        return $notification;
    }
}
