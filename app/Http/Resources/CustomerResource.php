<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'room_id' => $this->room_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'created_at' => $this->created_at,
        ];
    }
}
