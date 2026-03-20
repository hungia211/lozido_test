<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    // use SoftDeletes;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_DELETED = 'deleted';

    protected $fillable = [
        'name',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'int'
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }


    public function priceItems()
    {
        return $this->belongsToMany(PriceItem::class, 'room_price_item')
            ->withTimestamps();
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE,
            self::STATUS_OCCUPIED,
            self::STATUS_MAINTENANCE,
        ];
    }

    public function getPriceFormatAttribute()
    {
        return number_format((float) $this->price, 0, ',', '.') . ' VND';
    }

    public function getStatusTestAttribute()
    {
        $config = config('room');
        return $this->status == self::STATUS_AVAILABLE ? $config['aviable'] : config('room')['aviable'];
    }
}
