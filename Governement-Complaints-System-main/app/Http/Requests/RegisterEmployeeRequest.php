<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'nullable|string',
        'image' => 'nullable|image|max:2048',
        'government_entity_id' => 'required|exists:government_entities,id',
    ];
}
    public function messages()
    {
        return [
            'name.max' => 'الاسم يجب ألا يتجاوز ال 255 حرف',
            'email.unique' => 'البريد الإلكتروني مُستخدم مسبقًا!',
            'email.required' => 'يجب إدخال البريد الإلكتروني!',
            'password.required' => 'يجب إدخال كلمة المرور!',
            'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            'password.confirmed' => 'يجب تأكيد كلمة السر مرة ثانية',
            'government_entity_id.required' => 'يجب اختيار الجهة الحكومية!',
            'government_entity_id.exists' => 'الجهة الحكومية المختارة غير موجودة',
        ];
    }
}
