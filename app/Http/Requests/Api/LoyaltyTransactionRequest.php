<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoyaltyTransactionRequest extends FormRequest
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
            'type' => 'required|string|in:earn,redeem,bonus,adjustment',
            'points' => 'required|integer|min:1',
            'description' => 'required|string|max:255',
            'order_id' => 'nullable|string|max:100',
            'reference_type' => 'nullable|string|max:50',
            'reference_id' => 'nullable|integer',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Transaction type is required',
            'type.in' => 'Invalid transaction type. Must be earn, redeem, bonus, or adjustment',
            'points.required' => 'Points amount is required',
            'points.integer' => 'Points must be an integer',
            'points.min' => 'Points must be at least 1',
            'description.required' => 'Transaction description is required',
            'description.max' => 'Description cannot exceed 255 characters',
        ];
    }
}
