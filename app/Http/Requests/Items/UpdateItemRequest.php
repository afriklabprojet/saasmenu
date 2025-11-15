<?php

namespace App\Http\Requests\Items;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only vendors and employees can update items
        return Auth::check() && in_array(Auth::user()->type, [2, 4]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'cat_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999.99'],
            'original_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_available' => ['nullable', 'integer', 'in:0,1'],

            // Stock management
            'stock_management' => ['nullable', 'integer', 'in:0,1'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'min_order' => ['nullable', 'integer', 'min:1'],
            'max_order' => ['nullable', 'integer', 'min:0'],
            'low_qty' => ['nullable', 'integer', 'min:0'],

            // Other fields
            'tax' => ['nullable', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:100'],
            'reorder_id' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Item name is required',
            'name.max' => 'Item name cannot exceed 255 characters',
            'cat_id.required' => 'Category is required',
            'cat_id.exists' => 'Selected category does not exist',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be positive',
            'price.max' => 'Price cannot exceed 999,999.99',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'Image must be jpeg, png, jpg, or webp format',
            'image.max' => 'Image size cannot exceed 2MB',
            'is_available.in' => 'Invalid availability status',
            'stock_management.in' => 'Invalid stock management value',
            'qty.min' => 'Quantity must be positive',
            'min_order.min' => 'Minimum order must be at least 1',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate that category belongs to the same vendor
            $user = Auth::user();
            $vendorId = $user->type == 4 ? $user->vendor_id : $user->id;

            if ($this->cat_id) {
                $category = \App\Models\Category::find($this->cat_id);
                if ($category && $category->vendor_id != $vendorId) {
                    $validator->errors()->add('cat_id', 'This category does not belong to your restaurant');
                }
            }
        });
    }
}
