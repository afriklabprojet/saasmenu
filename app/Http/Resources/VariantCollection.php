<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class VariantCollection extends ResourceCollection
{
    public $collects = VariantResource::class;

    public function toArray(Request $request): array
    {
        return [
            'variants' => $this->collection,
        ];
    }
}
