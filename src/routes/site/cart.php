<?php

use Illuminate\Support\Facades\Route;

Route::group([
    "namespace" => "App\Http\Controllers\Vendor\VariationCart\Site",
    "middleware" => ["web"],
    "as" => "catalog.cart.",
    "prefix" => "cart",
], function () {
    Route::put("/add/{variation}", "CartController@addToCart")
        ->name("add");
    Route::get("/", "CartController@index")
        ->name("index");
    Route::delete("/{variation}", "CartController@deleteItem")
        ->name("delete");
    Route::put("/{variation}", "CartController@changeQuantity")
        ->name("update");
});