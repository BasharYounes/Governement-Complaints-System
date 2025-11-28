<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\AdPublishedEvent;
use App\Events\GenericNotificationEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    protected $guard_name = 'api';
    /**
     * الميزات المستخدمة في النموذج.
     */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'image',
        'password',
        'email_verified_at',
        'phone',
        'fcm_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'published_at' => 'datetime',
            'password' => 'hashed',
            'verified_at' => 'datetime',
        ];
    }





    /**
     * Accessor: return full image URL if stored path exists
     */
    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        if (is_string($value) && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/storage/'))) {
            return $value;
        }
        return Storage::url($value);
    }
    /**
     * العلاقة مع الشكاوى.
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
    /**
     * العلاقة مع سجلات تدقيق الشكاوى.
     */
    public function complaintAuditLogs()
    {
        return $this->morphMany(ComplaintAuditLog::class, 'auditable');
    }
    /**
     * العلاقة مع المرفقات.
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

}
