<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'room_id',
        'name',
        'phone',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
