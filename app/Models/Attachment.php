<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    /**
     * الحقول القابلة للتعبئة.
     */
    protected $fillable = [
    'complaint_id',
    'file_path',
    'file_name',
    'mime_type',
    'file_size',
    'uploaded_by'
    ];

    /**
     * العلاقة مع الشكوى.
     */
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
    /**
     * العلاقة مع المستخدم الذي رفع المرفق.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
