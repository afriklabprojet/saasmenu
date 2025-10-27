<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
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
            'item_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'modifiers' => 'nullable|array',
            'modifiers.*' => 'array',
            'modifiers.*.name' => 'required_with:modifiers.*|string|max:255',
            'modifiers.*.price' => 'required_with:modifiers.*|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'Item ID is required',
            'name.required' => 'Item name is required',
            'price.required' => 'Item price is required',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price cannot be negative',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be an integer',
            'quantity.min' => 'Quantity must be at least 1',
        ];
    }
}
