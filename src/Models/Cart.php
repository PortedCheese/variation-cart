<?php

namespace PortedCheese\VariationCart\Models;

use App\ProductVariation;
use App\CartProductVariationSet;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [];

    /**
     * Вариации.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function variations()
    {
        return $this->belongsToMany(ProductVariation::class)
            ->withPivot("quantity")
            ->withTimestamps();
    }

    /**
     * Сеты дополнений
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sets(){
        return $this->hasMany(CartProductVariationSet::class);
    }

    /**
     * Количество комплектов вариации в корзине
     *
     * @param ProductVariation $variation
     * @return int
     */
    public function reserveCount(ProductVariation $variation){
        return $this->sets()->where('product_variation_id','=',$variation->id)->count();
    }

    /**
     * Количество одиночных варриаций в корзине
     *
     * @param ProductVariation $variation
     * @return int
     */
    public function aloneCount(ProductVariation $variation){
        $count = 0;
        $variations = $this->variations()->where('product_variation_id','=',$variation->id)->get();
        foreach ($variations as $item) {
            $pivot = $item->pivot;
            $count += $pivot->quantity;
        }
        return $count;
    }

    /**
     * Количество элементов.
     *
     * @return int
     */
    public function getCountAttribute()
    {
        $count = 0;
        foreach ($this->variations as $variation) {
            $pivot = $variation->pivot;
            $count += $pivot->quantity;
        }
        return $count;
    }

    /**
     * Формат итого.
     *
     * @return string
     */
    public function getHumanTotalAttribute()
    {
        if ($this->total - intval($this->total) > 0) {
            return number_format($this->total, 2, ",", " ");
        }
        else {
            return number_format($this->total, 0, ",", " ");
        }
    }

    /**
     * Цена без скидки.
     *
     * @return float|int
     */
    public function getSaleLessAttribute()
    {
        $price = 0;
        foreach ($this->variations as $variation) {
            $pivot = $variation->pivot;
            $quantity = $pivot->quantity;
            if ($variation->sale) {
                $price += $variation->sale_price * $quantity;
            }
            else {
                $price += $variation->price * $quantity;
            }
        }
        foreach ($this->sets as $set){
            foreach ($set->addons as $addon){
                $quantity = $addon->quantity;
                if ($addon->variation->sale) {
                    $price += $addon->variation->sale_price * $quantity;
                }
                else {
                    $price += $addon->variation->price * $quantity;
                }
            }
        }
        return $price;
    }

    /**
     * Формат цены без скидки.
     *
     * @return string
     */
    public function getHumanSaleLessAttribute()
    {
        $price = $this->sale_less;
        if ($price - intval($price) > 0) {
            return number_format($price, 2, ",", " ");
        }
        else {
            return number_format($price, 0, ",", " ");
        }
    }

    /**
     * Скидка.
     *
     * @return float|int
     */
    public function getDiscountAttribute()
    {
        $price = 0;
        foreach ($this->variations as $variation) {
            $pivot = $variation->pivot;
            $quantity = $pivot->quantity;
            $price += $variation->discount * $quantity;
        }

        foreach ($this->sets as $set){
            foreach ($set->addons as $addon){
                $quantity = $addon->quantity;
                $price += $addon->variation->discount * $quantity;
            }
        }


        return $price;
    }

    /**
     * Формат скидки.
     *
     * @return string
     */
    public function getHumanDiscountAttribute()
    {
        $price = $this->discount;
        if ($price - intval($price) > 0) {
            return number_format($price, 2, ",", " ");
        }
        else {
            return number_format($price, 0, ",", " ");
        }
    }
}
