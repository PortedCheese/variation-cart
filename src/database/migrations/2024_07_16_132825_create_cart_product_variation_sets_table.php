<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartProductVariationSetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_product_variation_sets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("cart_id")
                ->comment("Корзина");

            $table->unsignedBigInteger("product_variation_id")
                ->comment("Вариация товара");

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
        Schema::dropIfExists('cart_product_variation_sets');
    }
}
