<?php

namespace PortedCheese\VariationCart\Facades;

use App\Cart;
use App\ProductVariation;
use Illuminate\Support\Facades\Facade;
use PortedCheese\VariationCart\Helpers\CartActionsManager;

/**
 * @method static object getCartInfo(Cart $cart = null)
 * @method static Cart addToCart(ProductVariation $variation, $quantity = 1)
 * @method static recalculateTotal(Cart $cart)
 * @method static setCookie(Cart $cart)
 * @method static Cart initCart()
 * @method static Cart|bool getCart()
 *
 * @see CartActionsManager
 */
class CartActions extends Facade
{
    protected static function getFacadeAccessor()
    {
        return "cart-actions";
    }
}