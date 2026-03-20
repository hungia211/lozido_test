<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PriceItem;

class CreatePriceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price');
            $table->timestamps();
        });

        $Services = [
            ['name' => 'Điện', 'price' => 3500],
            ['name' => 'Nước', 'price' => 15000],
            ['name' => 'Internet', 'price' => 200000],
            ['name' => 'Rác', 'price' => 30000],
        ];
        foreach ($Services as $service) {
            factory(PriceItem::class)->create($service);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_items');
    }
}
