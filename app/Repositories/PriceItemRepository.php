<?php

namespace App\Repositories;

use App\Models\PriceItem;

class PriceItemRepository implements PriceItemRepositoryInterface
{
    public function getAll()
    {
        return PriceItem::all();
    }

    public function findById(int $id): ?PriceItem
    {
        return PriceItem::find($id);
    }

    public function create(array $data)
    {
        return PriceItem::create([
            'name' => $data['name'],
            'price' => $data['price'],
        ]);
    }

    public function delete(int $id): bool
    {
        return PriceItem::where('id', $id)->delete() > 0;
    }

    public function countRoomsUsingPriceItem(int $id): int
    {
        $priceItem = PriceItem::withCount('rooms')->find($id);

        return $priceItem ? $priceItem->rooms_count : 0;
    }
}
