<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    /**
     * استخدام خاصية المصنع للنماذج.
     */
    use HasFactory;
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'user_id',
        'government_entity_id',
        'description',
        'status',
        'reference_number',
        'location',
        'type'
    ];
    /**
     * الحقول التي يجب تحويلها إلى أنواع معينة.
     */
    protected $casts = [
        'location' => 'array'
    ];
    /**
     * العلاقة مع المستخدم الذي قدم الشكوى.
     */
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
        return $this->hasMany(ComplaintAuditLog::class, 'complaint_id');
    }
}
