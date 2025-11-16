<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ComplaintAuditDetail;

class ComplaintAuditLog extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
        'complaint_id',
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent'
    ];
    /**
     * العلاقة مع الشكوى.
     */
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function details()
    {
        return $this->hasMany(ComplaintAuditDetail::class, 'audit_log_id');
    }

    /**
     * العلاقة مع المستخدم الذي قام بالإجراء.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
