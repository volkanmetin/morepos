<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
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
            'uuid' => $this->uuid,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->customer->name,
            'payment_method' => $this->payment_method,
            'coupon_id' => $this->coupon_id,
            'manual_discount' => $this->manual_discount,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'items' => $this->items,
            'items_count' => $this->items->sum('quantity'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
