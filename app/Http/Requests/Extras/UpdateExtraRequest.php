<?php

namespace App\Http\Requests\Extras;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateExtraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only vendors (type 2) and employees (type 4) can update extras
        return in_array(Auth::user()->type, [2, 4]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'is_available' => 'nullable|boolean',
            'reorder_id' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Extra name is required',
            'name.max' => 'Extra name cannot exceed 255 characters',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be positive',
            'price.max' => 'Price cannot exceed 999999.99',
        ];
    }
}
