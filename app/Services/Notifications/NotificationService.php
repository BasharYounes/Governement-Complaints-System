<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;


class NotificationService
{
    public function send(User $user, string $type, array $data = []): void
    {
        $content = $this->getTemplate($type, $data);

        $this->sendFCM($user, $content);

        $this->saveToDatabase($user, $content);
    }

    private function getTemplate(string $type, array $data): array
    {
        $template = config("notifications.templates.$type");

        if (!$template) {
            $template = [
                'title' => 'إشعار جديد',
                'body' => 'لديك إشعار جديد'
            ];
        }

        return [
            'title' => $this->replacePlaceholders($template['title'], $data),
            'body' => $this->replacePlaceholders($template['body'], $data)
        ];
    }

    protected function replacePlaceholders(string $text, array $data): string
    {
        // إذا لم تكن هناك متغيرات في النص، أعد النص كما هو
        if (empty($data) || !preg_match('/\{\{[^}]+\}\}/', $text)) {
            return $text;
        }

        // استبدل المتغيرات الموجودة
        foreach ($data as $key => $value) {
            $text = str_replace("{{$key}}", $value, $text);
        }

        return $text;
    }

    private function sendFCM(User $user, array $content): void
    {
        try {
            if (empty($user->fcm_token)) {
                \Log::warning('المستخدم لا يملك FCM token: ' . $user->id);
                return;
            }

            \Log::info('محاولة إرسال إشعار للمستخدم: ' . $user->id . ' مع token: ' . substr($user->fcm_token, 0, 20) . '...');

            $messaging = app('firebase.messaging');

            $message = CloudMessage::withTarget('token',
                $user->fcm_token)
                ->withNotification([
                    'title' => $content['title'],
                    'body' => $content['body']
                ]);

            \Log::info('تم إنشاء الرسالة بنجاح، محاولة الإرسال...');

            $result = $messaging->send($message);

            \Log::info('تم إرسال الإشعار بنجاح للمستخدم: ' . $user->id . ' - النتيجة: ' . json_encode($result));
        } catch (\Exception $e) {
            \Log::error('فشل إرسال الإشعار للمستخدم ' . $user->id . ': ' . $e->getMessage());
            \Log::error('تفاصيل الخطأ: ' . $e->getTraceAsString());
        }
    }


    private function saveToDatabase(User $user, array $content): void
    {
        Notification::create([
            'user_id' => $user->id,
            'title' => $content['title'],
            'body' => $content['body']
        ]);
    }
}
