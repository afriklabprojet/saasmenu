<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'payment_method' => 'required|string|in:cash,card,digital',
            'amount' => 'required|numeric|min:0.01',
            'table_number' => 'nullable|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'tip_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Invalid payment method. Must be cash, card, or digital',
            'amount.required' => 'Payment amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be greater than 0',
            'table_number.integer' => 'Table number must be an integer',
            'table_number.min' => 'Table number must be at least 1',
            'discount.numeric' => 'Discount must be a number',
            'discount.min' => 'Discount cannot be negative',
            'tip_amount.numeric' => 'Tip amount must be a number',
            'tip_amount.min' => 'Tip amount cannot be negative',
        ];
    }
}
