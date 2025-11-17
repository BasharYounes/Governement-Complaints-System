<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserInformationRequest extends FormRequest
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
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|max:255|unique:users',
            'image' => 'sometimes|mimes:jpeg,png,jpg,gif,svg',        
            'phone' => 'sometimes|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.max' => 'الاسم يجب ألا يتجاوز ال 255 حرف',
            'email.unique' => 'البريد الإلكتروني مُستخدم مسبقًا!',
            'email.required' => 'يجب إدخال البريد الإلكتروني!'
        ];
    }
}
