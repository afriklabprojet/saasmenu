<?php

namespace App\Http\Requests\Variants;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(Auth::user()->type, [2, 4]);
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0|max:999999.99',
            'original_price' => 'nullable|numeric|min:0|max:999999.99',
            'qty' => 'nullable|integer|min:0',
            'min_order' => 'nullable|integer|min:1',
            'max_order' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'stock_management' => 'nullable|boolean',
            'reorder_id' => 'nullable|integer|min:0',
        ];
    }
}
