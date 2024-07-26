<?php

namespace PortedCheese\VariationCart\Models;

use App\ProductVariation;
use Illuminate\Database\Eloquent\Model;

class CartProductVariationSetAddon extends Model
{
    protected $fillable = [
        "cart_id",
        "card_product_variation_set_id",
        "product_variation_id",
        "quantity"
    ];

    /**
     * Вариация.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variation()
    {
        return $this->belongsTo(ProductVariation::class,"product_variation_id","id")
            ->with("product", "product.cover")
            ->orderBy("price");
    }

    /**
     * Корзина
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function cart()
    {
        return $this->belongsTo(\App\Cart::class);
    }

    /**
     * Комплект
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function set(){
        return $this->belongsTo(\App\CartProductVariationSet::class,"cart_product_variation_set_id");
    }

}
