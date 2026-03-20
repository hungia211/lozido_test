<?php

namespace App\Repositories;

use App\Models\Room;
use Illuminate\Support\Facades\DB;

class RoomRepository implements RoomRepositoryInterface
{

    public function getAll()
    {
        return Room::where('status', '!=', Room::STATUS_DELETED)->get();
    }

    public function findById(int $id): ?Room
    {
        return Room::where('status', '!=', Room::STATUS_DELETED)->find($id);
    }

    public function create(array $data): Room
    {
        return Room::create([
            'name' => $data['name'],
            'price' => $data['price'],
            'status' => Room::STATUS_AVAILABLE,
        ]);
    }

    public function softDelete(int $id): bool
    {
        return Room::where('id', $id)
            ->where('status', '!=', Room::STATUS_DELETED)
            ->update([
                'status' => Room::STATUS_DELETED,
            ]) > 0;
    }

    public function attachPriceItems(Room $room, array $priceItemIds): void
    {
        $room->priceItems()->attach($priceItemIds);
    }

    public function attachOnePriceItem(Room $room, int $priceItemId): void
    {
        $room->priceItems()->syncWithoutDetaching([$priceItemId]);
    }

    public function detachOnePriceItem(int $roomId, int $priceItemId): void
    {
        $room = Room::findOrFail($roomId);
        $room->priceItems()->detach($priceItemId);
    }

    public function updateStatus(int $id, string $status): bool
    {
        return Room::where('id', $id)
            ->where('status', '!=', Room::STATUS_DELETED)
            ->update([
                'status' => $status,
            ]) > 0;
    }

    public function countCustomers(int $roomId): int
    {
        $room = Room::withCount('customers')->find($roomId);

        return $room ? $room->customers_count : 0;
    }

    public function loadRelations(Room $room): Room
    {
        return $room->load(['customers', 'priceItems']);
    }
}
