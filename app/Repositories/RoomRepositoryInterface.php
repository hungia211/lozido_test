<?php

namespace App\Repositories;

use App\Models\Room;

interface RoomRepositoryInterface
{

    public function getAll();

    public function findById(int $id): ?Room;

    public function create(array $data): Room;

    public function softDelete(int $id): bool;

    public function attachPriceItems(Room $room, array $priceItemIds): void;

    public function attachOnePriceItem(Room $room, int $priceItemId): void;

    public function detachOnePriceItem(int $roomId, int $priceItemId): void;

    public function updateStatus(int $id, string $status): bool;

    public function countCustomers(int $roomId): int;

    public function loadRelations(Room $room): Room;
}
