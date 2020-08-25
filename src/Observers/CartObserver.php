<?php

namespace PortedCheese\VariationCart\Observers;

use App\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PortedCheese\VariationCart\Facades\CartActions;

class CartObserver
{
    /**
     * Перед сохранением.
     *
     * @param Cart $cart
     */
    public function creating(Cart $cart)
    {
        if (Auth::check()) {
            $cart->user_id = Auth::id();
        }
        $cart->uuid = Str::uuid();
    }

    /**
     * После создания.
     *
     * @param Cart $cart
     */
    public function created(Cart $cart)
    {
        CartActions::setCookie($cart);
    }

    /**
     * После обновления.
     *
     * @param Cart $cart
     */
    public function updated(Cart $cart)
    {
        CartActions::setCookie($cart);
        CartActions::clearCartCache($cart);
    }

    /**
     * Перед удалением.
     *
     * @param Cart $cart
     */
    public function deleting(Cart $cart)
    {
        $cart->variations()->sync([]);
        CartActions::clearCartCache($cart);
    }
}
