<?php

namespace App\Http\Requests\Attachments;

use Illuminate\Foundation\Http\FormRequest;

class AttachmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|max:10240', // الحد الأقصى لحجم الملف 10 ميجابايت
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'يجب إرفاق ملف.',
            'file.file' => 'يجب أن يكون الملف المرفق ملفًا صالحًا.',
            'file.max' => 'حجم الملف المرفق يجب ألا يتجاوز 10 ميجابايت.',
            'complaint_id.required' => 'معرف الشكوى مطلوب.',
            'complaint_id.exists' => 'الشكوى المحددة غير موجودة.',
        ];
    }
}
