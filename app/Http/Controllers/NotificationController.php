<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Repositories\NotificationRepository;

use App\Http\Requests\MarkAsReadRequest;
use App\Traits\ApiResponse;


class NotificationController extends Controller
{
    use ApiResponse;
    public function __construct(public NotificationRepository $notificationRepository) {}

    public function index(Request $request)
    {
        return $request->user()->notifications()->orderBy('created_at', 'desc')->get();
    }

    public function show($id)
    {
        $user = auth()->user();

        $notification = $this->notificationRepository->findNotification($id, $user);

        $notification = $this->notificationRepository->MarkAsReadNotification($notification);

        return $this->success('تم جلب تفاصيل الإشعار بنجاح',$notification);
    }

    public function markAsRead(MarkAsReadRequest $request)
    {
        $user = auth()->user();

        if ($request->mark_all) {

            $this->notificationRepository->MarkAsReadAllNotification($user);

            return $this->success(
                'All notifications marked as read',
                ['unread_count' => $user->notifications()->where('is_read', false)->count()]
            );
        }

        $notification = $this->notificationRepository->findNotification($request->notification_id,$user);

        if (!$notification->is_read)
        {
            $this->notificationRepository->MarkAsReadNotification($notification);
        }

        return $this->success('Notification marked as read',
            ['unread_count' => $user->notifications()->where('is_read', false)->count()]
        );
    }



}
