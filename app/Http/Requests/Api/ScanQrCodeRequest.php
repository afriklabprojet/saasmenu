<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ScanQrCodeRequest extends FormRequest
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
            'qr_code' => 'required|string|max:255',
            'customer_id' => 'nullable|integer|exists:users,id',
            'session_id' => 'nullable|string|max:255',
            'user_agent' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'qr_code.required' => 'QR code is required',
            'qr_code.string' => 'QR code must be a string',
            'customer_id.exists' => 'Customer does not exist',
            'session_id.string' => 'Session ID must be a string',
            'user_agent.string' => 'User agent must be a string',
        ];
    }
}
