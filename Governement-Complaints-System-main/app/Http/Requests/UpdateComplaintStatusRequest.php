<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintStatusRequest extends FormRequest
{
   public function authorize(): bool
{
    return true;
}

    public function rules(): array
    {
        return [
            'status' => 'required|in:new,in_progress,completed,rejected',
            'notes'  => 'sometimes|nullable|string', 
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'يجب إدخال الحالة.',
            'status.in'       => 'القيمة المرسلة للحالة غير صالحة.',
            'notes.string'    => 'الملاحظات يجب أن تكون نصية.',
        ];
    }
}
