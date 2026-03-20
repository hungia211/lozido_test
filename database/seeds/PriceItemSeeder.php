<?php

namespace Database\Seeders;

use App\Models\PriceItem;
use Illuminate\Database\Seeder;

class PriceItemSeeder extends Seeder
{
    public function run()
    {
        $items = [
            ['name' => 'Điện', 'price' => 3500],
            ['name' => 'Nước', 'price' => 15000],
            ['name' => 'Internet', 'price' => 200000],
            ['name' => 'Rác', 'price' => 30000],
        ];

        foreach ($items as $item) {
            PriceItem::firstOrCreate(
                ['name' => $item['name']],
                ['price' => $item['price']]
            );
        }
    }
}
