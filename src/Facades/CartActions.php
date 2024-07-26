<?php

namespace PortedCheese\VariationCart\Facades;

use App\Cart;
use App\CartProductVariationSet;
use App\CartProductVariationSetAddon;
use App\Order;
use App\ProductVariation;
use Illuminate\Support\Facades\Facade;
use PortedCheese\VariationCart\Helpers\CartActionsManager;

/**
 * @method static Cart initCart()
 * @method static Cart|bool getCart()
 * @method static setCookie(Cart $cart)
 *
 * @method static object getCartInfo(Cart $cart = null)
 * @method static array|bool getCartItems(Cart $cart = null)
 *
 * @method static Cart addToCart(ProductVariation $variation, $quantity = 1)
 * @method static Cart changeQuantity(ProductVariation $variation, $quantity = 1, Cart $customCart = null)
 * @method static Cart changeAddonQuantity($quantity = 1, $setId = 0, $addonId = 0, Cart $customCart = null)
 * @method static deleteItem(ProductVariation $variation)
 * @method static deleteSet(CartProductVariationSet $set)
 * @method static deleteAddonItem(CartProductVariationSetAddon $addon)
 * @method static clearCart(Cart $cart)
 *
 * @method static Order makeOrder(Cart $cart, array $userData)
 *
 * @method static recalculateTotal(Cart $cart)
 * @method static clearCartCache(Cart $cart)
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