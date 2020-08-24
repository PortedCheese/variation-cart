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
});