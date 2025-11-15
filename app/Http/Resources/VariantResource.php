<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_id' => $this->item_id,
            'name' => $this->name,
            'price' => number_format($this->price, 2),
            'original_price' => number_format($this->original_price, 2),
            'qty' => $this->qty,
            'min_order' => $this->min_order,
            'max_order' => $this->max_order,
            'is_available' => $this->is_available,
            'stock_management' => $this->stock_management,
            'reorder_id' => $this->reorder_id,

            'item' => $this->whenLoaded('item', function () {
                return [
                    'id' => $this->item->id,
                    'name' => $this->item->name,
                    'slug' => $this->item->slug,
                ];
            }),

            'timestamps' => [
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}
