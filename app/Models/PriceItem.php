<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceItem extends Model
{
    protected $fillable = [
        'name',
        'price',
    ];

    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_price_item')
            ->withTimestamps();
    }
}
