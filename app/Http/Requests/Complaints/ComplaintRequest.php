<?php

namespace App\Http\Requests\Complaints;

use Illuminate\Foundation\Http\FormRequest;

class ComplaintRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'government_entity_id' => 'required|exists:government_entities,id',
            'description' => 'required|string|max:1000',
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'location' => 'required|array',
            'type' => 'required|string|max:255',

        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The specified user does not exist.',
            'government_entity_id.required' => 'The government entity ID is required.',
            'government_entity_id.exists' => 'The specified government entity does not exist.',
            'description.required' => 'The description is required.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'status.required' => 'The status is required.',
            'status.in' => 'The status must be one of the following: pending, in_progress, resolved, closed.',
            'location.required' => 'The location is required.',
            'location.array' => 'The location must be an array.',
            'type.required' => 'The type is required.',
            'type.string' => 'The type must be a string.',
            'type.max' => 'The type may not be greater than 255 characters.',
        ];
    }
}
