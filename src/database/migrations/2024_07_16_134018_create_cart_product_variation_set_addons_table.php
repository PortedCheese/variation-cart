<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartProductVariationSetAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_product_variation_set_addons', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("cart_id")
                ->comment("Корзина");

            $table->unsignedBigInteger("cart_product_variation_set_id")
                ->comment("Комплект");

            $table->unsignedBigInteger("product_variation_id")
                ->comment("Вариация дополнения");

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
        Schema::dropIfExists('cart_product_variation_set_addons');
    }
}
