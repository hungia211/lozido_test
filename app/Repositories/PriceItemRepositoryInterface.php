<?php

namespace App\Repositories;

use App\Models\PriceItem;

interface PriceItemRepositoryInterface
{
    public function getAll();

    public function findById(int $id): ?PriceItem;

    public function create(array $data);

    public function delete(int $id): bool;

    public function countRoomsUsingPriceItem(int $id): int;
}
