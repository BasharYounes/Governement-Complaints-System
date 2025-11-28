<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Events\AdPublishedEvent;
use App\Events\GenericNotificationEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
     protected $guard_name = 'employee-api';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'image',
        'government_entity_id',
        'position',
    ];

     protected $hidden = [
        'password',
        'remember_token',
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

      // one to many
    public function governmentEntity()
    {
        return $this->belongsTo(GovernmentEntities::class, 'government_entity_id');
    }

    public function complaintAuditLogs()
    {
        return $this->morphMany(ComplaintAuditLog::class, 'auditable');
    }



}
