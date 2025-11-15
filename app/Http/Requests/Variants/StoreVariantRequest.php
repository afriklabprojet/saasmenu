<?php

namespace App\Http\Requests\Variants;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;

class StoreVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(Auth::user()->type, [2, 4]);
    }

    public function rules(): array
    {
        return [
            'item_id' => 'required|integer|exists:items,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'original_price' => 'nullable|numeric|min:0|max:999999.99',
            'qty' => 'nullable|integer|min:0',
            'min_order' => 'nullable|integer|min:1',
            'max_order' => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'stock_management' => 'nullable|boolean',
            'reorder_id' => 'nullable|integer|min:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $vendorId = Auth::user()->type == 4 ? Auth::user()->vendor_id : Auth::user()->id;

            $item = Item::where('id', $this->item_id)
                ->where('vendor_id', $vendorId)
                ->where('is_deleted', 0)
                ->first();

            if (!$item) {
                $validator->errors()->add('item_id', 'You can only add variants to your own items');
            }
        });
    }
}
