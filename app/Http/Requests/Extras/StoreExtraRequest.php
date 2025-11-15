<?php

namespace App\Http\Requests\Extras;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;

class StoreExtraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only vendors (type 2) and employees (type 4) can create extras
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
            'item_id' => 'required|integer|exists:items,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
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
            'item_id.required' => 'Item ID is required',
            'item_id.exists' => 'The selected item does not exist',
            'name.required' => 'Extra name is required',
            'name.max' => 'Extra name cannot exceed 255 characters',
            'price.required' => 'Price is required',
            'price.min' => 'Price must be positive',
            'price.max' => 'Price cannot exceed 999999.99',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verify item belongs to vendor
            $vendorId = Auth::user()->type == 4 ? Auth::user()->vendor_id : Auth::user()->id;

            $item = Item::where('id', $this->item_id)
                ->where('vendor_id', $vendorId)
                ->where('is_deleted', 0)
                ->first();

            if (!$item) {
                $validator->errors()->add('item_id', 'You can only add extras to your own items');
            }
        });
    }
}
