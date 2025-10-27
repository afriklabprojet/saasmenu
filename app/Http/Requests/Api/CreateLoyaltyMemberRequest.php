<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateLoyaltyMemberRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'birthday' => 'nullable|date|before:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Member name is required',
            'phone.required' => 'Phone number is required',
            'email.email' => 'Please provide a valid email address',
            'birthday.date' => 'Birthday must be a valid date',
            'birthday.before' => 'Birthday must be before today',
            'user_id.exists' => 'User does not exist',
        ];
    }
}
