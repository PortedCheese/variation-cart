<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartProductVariationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_product_variation', function (Blueprint $table) {
            $table->unsignedBigInteger("cart_id")
                ->comment("Корзина");

            $table->unsignedBigInteger("product_variation_id")
                ->comment("Вариация");

            $table->integer("quantity")
                ->default(1)
                ->comment("Количество");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_product_variation');
    }
}
