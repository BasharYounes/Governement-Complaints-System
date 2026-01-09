<?php

namespace App\Http\Requests\Complaints;

use Illuminate\Foundation\Http\FormRequest;

class ExportComplaintsRequest extends FormRequest
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
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d|after_or_equal:from_date',
            'month' => 'nullable|date_format:Y-m',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'from_date.date_format' => 'تاريخ البداية يجب أن يكون بصيغة YYYY-MM-DD',
            'to_date.date_format' => 'تاريخ النهاية يجب أن يكون بصيغة YYYY-MM-DD',
            'to_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون مساوياً أو بعد تاريخ البداية',
            'month.date_format' => 'الشهر يجب أن يكون بصيغة YYYY-MM',
        ];
    }
}
