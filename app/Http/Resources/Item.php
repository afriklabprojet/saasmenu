<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Item extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'cat_id' => $this->cat_id,
            'item_name' => $this->item_name,
            'description' => $this->description,
            'item_price' => number_format($this->item_price, 2),
            'item_original_price' => number_format($this->item_original_price, 2),
            'tax' => number_format($this->tax, 2),
            'is_favorite' => $this->is_favorite,
            'has_variants' => $this->has_variants,
            'variants' => $this['variation'],
            'extras' => $this['extras'],
            'image_name' => $this['item_image']->image_name,
            'image' => $this['item_image']->image_url,
        ];
    }
}
