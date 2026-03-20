<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'price_format' => $this->price_format,
            'status_test' => $this->status_test,
            'status' => $this->status,
            'customers' => CustomerResource::collection($this->whenLoaded('customers')),
            'price_items' => PriceItemResource::collection($this->whenLoaded('priceItems')),
            'created_at' => $this->created_at,
        ];
    }
}
