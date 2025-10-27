<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\helper;

class OrderDetails extends JsonResource
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
            'order_id' => $this->order_id,
            'item_id' => $this->item_id,
            'image' => helper::image_path($this->item_image),
            'item_name' => $this->item_name,
            'extras_id' => $this->extras_id,
            'extras_name' => $this->extras_name,
            'extras_price' => $this->extras_price,
            'price' => $this->price,
            'variants_id' => $this->variants_id,
            'variants_name' => $this->variants_name,
            'variants_price' => $this->variants_price,
            'qty' => $this->qty,
        ];
    }
}
