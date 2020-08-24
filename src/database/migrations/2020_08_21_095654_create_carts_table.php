<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("user_id")
                ->nullable()
                ->comment("Владелец корзины");

            $table->dateTime("notify_at")
                ->nullable()
                ->comment("Отправлено уведомление об устаревшей корзине");

            $table->uuid("uuid")
                ->comment("Идентификатор корзины");

            $table->unsignedDecimal("total")
                ->default(0)
                ->comment("Итого");

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
        Schema::dropIfExists('carts');
    }
}
