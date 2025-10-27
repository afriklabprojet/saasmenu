<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\helper;

class Cart extends JsonResource
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
            'item_id' => $this->item_id,
            'item_name' => $this->item_name,
            'item_image' => $this->item_image,
            'item_image_url' => helper::image_path($this->item_image),
            'item_price' => $this->item_price,
            'extras_id' => $this->extras_id,
            'extras_name' => $this->extras_name,
            'extras_price' => $this->extras_price,
            'qty' => $this->qty,
            'price' => $this->price,
            'tax' => $this->tax,
            'variants_id' => $this->variants_id,
            'variants_name' => $this->variants_name,
            'variants_price' => $this->variants_price
        ];
    }
}
