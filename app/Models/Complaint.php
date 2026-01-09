<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'government_entity_id',
        'description',
        'status',
        'reference_number',
        'location',
        'type'
    ];

    protected $casts = [
        'location' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * العلاقة مع الجهة الحكومية.
     */
    public function governmentEntity()
    {
        return $this->belongsTo(GovernmentEntities::class);
    }
    /**
     * العلاقة مع المرفقات.
     */
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
    public function auditLogs()
{
    return $this->hasMany(ComplaintAuditLog::class);
}

}
