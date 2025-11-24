<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintAuditDetail extends Model
{
    /**
     * استخدام خاصية المصنع للنماذج.
     */
    use HasFactory;
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'audit_log_id',
        'field_name',
        'old_value',
        'new_value',
        'notes',
    ];

    /**
     * العلاقة مع سجل تدقيق الشكوى.
     */
    public function auditLog()
    {
        return $this->belongsTo(ComplaintAuditLog::class, 'audit_log_id');
    }

    public function details()
{
    return $this->hasMany(ComplaintAuditDetail::class, 'audit_log_id', 'id');
}


}
