<?php

namespace PortedCheese\VariationCart\Models;

use Illuminate\Database\Eloquent\Model;

class CartProductVariationSet extends Model
{
    protected $fillable = ["cart_id","product_variation_id"];

    /**
     * Дополнительные позиции
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addons()
    {
        return $this->hasMany(\App\CartProductVariationSetAddon::class,"cart_product_variation_set_id","id");
    }

    /**
     * Вариация
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variation()
    {
        return $this->belongsTo(\App\ProductVariation::class, 'product_variation_id');
    }

    /**
     * Корзина
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart()
    {
        return $this->belongsTo(\App\Cart::class, 'product_variation_id');
    }

}
