<?php

namespace App\Services;

use App\Events\GenericNotificationEvent;
use App\Models\User;
use App\Models\Admin;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class LoginAttemptService
{
    /**
     * الحد الأقصى لعدد محاولات تسجيل الدخول الفاشلة قبل إيقاف الحساب
     */
    private const MAX_FAILED_ATTEMPTS = 5;

    /**
     * تسجيل محاولة تسجيل دخول فاشلة
     *
     * @param Model $user
     * @return void
     */
    public function recordFailedAttempt(Model $user): void
    {
        $failedAttempts = ($user->failed_login_attempts ?? 0) + 1;
        
        $user->update([
            'failed_login_attempts' => $failedAttempts,
        ]);

        // إذا تجاوز عدد المحاولات الفاشلة الحد المسموح
        if ($failedAttempts >= self::MAX_FAILED_ATTEMPTS) {
            $this->lockAccount($user);
        }
    }

    /**
     * إعادة تعيين محاولات تسجيل الدخول الفاشلة عند نجاح تسجيل الدخول
     *
     * @param Model $user
     * @return void
     */
    public function resetFailedAttempts(Model $user): void
    {
        if ($user->failed_login_attempts > 0 || $user->is_locked) {
            $user->update([
                'failed_login_attempts' => 0,
                'is_locked' => false,
                'locked_at' => null,
            ]);
        }
    }

    /**
     * التحقق من حالة الحساب (مقفل أم لا)
     *
     * @param Model $user
     * @return bool
     */
    public function isAccountLocked(Model $user): bool
    {
        return $user->is_locked ?? false;
    }

    /**
     * إيقاف الحساب وإرسال إشعار
     *
     * @param Model $user
     * @return void
     */
    private function lockAccount(Model $user): void
    {
        // إعادة تحميل البيانات للتأكد من الحصول على القيم المحدثة
        $user->refresh();
        
        if ($user->is_locked) {
            return; // الحساب مقفل بالفعل
        }

        $user->update([
            'is_locked' => true,
            'locked_at' => now(),
        ]);

        // إرسال إشعار للمستخدم
        $this->sendLockNotification($user);

        Log::warning("تم إيقاف الحساب بسبب محاولات تسجيل دخول فاشلة متعددة", [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_type' => $this->getUserType($user),
        ]);
    }

    /**
     * إرسال إشعار عند إيقاف الحساب
     *
     * @param Model $user
     * @return void
     */
    private function sendLockNotification(Model $user): void
    {
        // فقط للمستخدمين من نوع User يمكن إرسال إشعارات لهم
        if ($user instanceof User) {
            event(new GenericNotificationEvent(
                $user,
                'account_locked',
                [
                    'name' => $user->name,
                    'attempts' => self::MAX_FAILED_ATTEMPTS,
                ]
            ));
        }
    }

    /**
     * الحصول على نوع المستخدم
     *
     * @param Model $user
     * @return string
     */
    private function getUserType(Model $user): string
    {
        if ($user instanceof User) {
            return 'user';
        } elseif ($user instanceof Admin) {
            return 'admin';
        } elseif ($user instanceof Employee) {
            return 'employee';
        }
        return 'unknown';
    }

    /**
     * الحصول على الحد الأقصى لعدد المحاولات
     *
     * @return int
     */
    public function getMaxAttempts(): int
    {
        return self::MAX_FAILED_ATTEMPTS;
    }
}

