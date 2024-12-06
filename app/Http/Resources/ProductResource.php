<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'sale_price' => $this->sale_price,
            'purchase_price' => $this->purchase_price,
            'discounted_price' => $this->discounted_price,
            'image' => $this->image,
            'stock' => $this->stock,
            'category' => $this->category,
            'vendor' => $this->vendor,
            'brand' => $this->brand,
            'variants' => $this->variants,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
