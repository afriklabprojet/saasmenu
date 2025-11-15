<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'mobile' => $this->mobile,
            ],
            'delivery' => [
                'address' => $this->address,
                'building' => $this->building,
                'landmark' => $this->landmark,
                'pincode' => $this->pincode,
            ],
            'order_info' => [
                'order_type' => $this->order_type,
                'delivery_type' => $this->delivery_type,
                'order_date' => $this->order_date,
                'delivery_time' => $this->delivery_time,
            ],
            'financial' => [
                'sub_total' => (float) $this->sub_total,
                'tax' => (float) $this->tax,
                'delivery_charge' => (float) $this->delivery_charge,
                'discount_amount' => (float) $this->discount_amount,
                'grand_total' => (float) $this->grand_total,
                'payment_type' => $this->payment_type,
                'payment_status' => $this->payment_status,
            ],
            'status' => [
                'id' => $this->status,
                'type' => $this->status_type,
                'name' => optional($this->customstatus)->name,
            ],
            'vendor' => [
                'id' => $this->vendor_id,
                'note' => $this->vendor_note,
            ],
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
