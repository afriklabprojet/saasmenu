<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'cat_id' => $this->cat_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => number_format($this->price, 2),
            'original_price' => number_format($this->original_price, 2),
            'image' => $this->image ? asset('storage/admin-assets/images/item/' . $this->image) : null,
            'is_available' => $this->is_available,
            'is_deleted' => $this->is_deleted,

            // Stock management
            'stock_management' => $this->stock_management,
            'qty' => $this->qty,
            'min_order' => $this->min_order,
            'max_order' => $this->max_order,
            'low_qty' => $this->low_qty,

            // Other fields
            'tax' => $this->tax,
            'sku' => $this->sku,
            'reorder_id' => $this->reorder_id,

            // Relations
            'category' => $this->whenLoaded('category_info', function () {
                return [
                    'id' => $this->category_info->id,
                    'name' => $this->category_info->name,
                    'slug' => $this->category_info->slug,
                ];
            }),

            'extras' => $this->whenLoaded('extras', function () {
                return $this->extras->map(function ($extra) {
                    return [
                        'id' => $extra->id,
                        'name' => $extra->name,
                        'price' => number_format($extra->price, 2),
                    ];
                });
            }),

            'variants' => $this->whenLoaded('variation', function () {
                return $this->variation->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'price' => number_format($variant->price, 2),
                        'original_price' => number_format($variant->original_price, 2),
                        'qty' => $variant->qty,
                        'is_available' => $variant->is_available,
                    ];
                });
            }),

            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}
