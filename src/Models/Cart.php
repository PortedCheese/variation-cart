<?php

namespace PortedCheese\VariationCart\Models;

use App\ProductVariation;
use Illuminate\Database\Eloquent\Model;
use PortedCheese\VariationCart\Facades\CartActions;

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
     * Количество элементов.
     *
     * @return int
     */
    public function getCountAttribute()
    {
        $count = 0;
        foreach (CartActions::getCartVariationsWithProducts($this) as $variation) {
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
        foreach (CartActions::getCartVariationsWithProducts($this) as $variation) {
            $pivot = $variation->pivot;
            $quantity = $pivot->quantity;
            if ($variation->sale) {
                $price += $variation->sale_price * $quantity;
            }
            else {
                $price += $variation->price * $quantity;
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
        foreach (CartActions::getCartVariationsWithProducts($this) as $variation) {
            $pivot = $variation->pivot;
            $quantity = $pivot->quantity;
            $price += $variation->discount * $quantity;
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
