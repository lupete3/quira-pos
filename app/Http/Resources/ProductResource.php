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
            'reference' => $this->reference,
            'name' => $this->name,
            'photo_url' => $this->photo_url,
            'has_photo' => $this->hasPhoto(),
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'brand' => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
            ],
            'unit' => [
                'id' => $this->unit?->id,
                'name' => $this->unit?->name,
                'short_name' => $this->unit?->short_name,
            ],
            'purchase_price' => $this->when($request->user()?->role_id !== 3, $this->purchase_price),
            'sale_price' => $this->sale_price,
            'stock_quantity' => $this->when(
                isset($this->store_stock),
                $this->store_stock
            ),
            'min_stock' => $this->min_stock,
            'stock_alert' => $this->stock_alert,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
