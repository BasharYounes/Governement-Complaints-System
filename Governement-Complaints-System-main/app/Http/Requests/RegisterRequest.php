<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users', 
            'password' => 'required|min:8|confirmed',
            'phone' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
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

        ];
    }
}
