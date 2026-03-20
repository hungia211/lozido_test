<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\PriceItem;
use Faker\Generator as Faker;

$factory->define(PriceItem::class, function (Faker $faker) {
    return [
        'name'  => $this->faker->unique()->word,
        'price' => $this->faker->randomFloat(2, 2000, 50000),
    ];
});
