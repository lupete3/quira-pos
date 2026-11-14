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
            'client' => [
                'id' => $this->client?->id,
                'name' => $this->client?->name,
                'phone' => $this->client?->phone,
            ],
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'store' => [
                'id' => $this->store?->id,
                'name' => $this->store?->name,
            ],
            'total_amount' => $this->total_amount,
            'total_paid' => $this->total_paid,
            'sale_date' => $this->sale_date,
            'status' => $this->status,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product?->id,
                        'name' => $item->product?->name,
                        'reference' => $item->product?->reference,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'total_price' => $item->total_price,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
