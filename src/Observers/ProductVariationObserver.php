<?php

namespace PortedCheese\VariationCart\Observers;

use App\Cart;
use App\ProductVariation;
use PortedCheese\VariationCart\Facades\CartActions;

class ProductVariationObserver
{
    /**
     * Перед обновлением.
     *
     * @param ProductVariation $variation
     */
    public function updated(ProductVariation $variation)
    {
        $original = $variation->getOriginal();
        $carts = $variation->carts;
        // Если выключили вариацию, убрать ее из всех корзин.
        if (empty($original["disabled_at"]) && ! empty($variation->disabled_at)) {
            $variation->carts()->sync([]);
        }
        foreach ($carts as $cart) {
            $this->changeCart($cart);
        }
    }

    /**
     * После удаления.
     *
     * @param ProductVariation $variation
     */
    public function deleted(ProductVariation $variation)
    {
        $carts = $variation->carts;
        $variation->carts()->sync([]);
        foreach ($carts as $cart) {
            $this->changeCart($cart);
        }
    }

    /**
     * Изменить корзины после изменения вариаций.
     *
     * @param Cart $cart
     */
    protected function changeCart(Cart $cart)
    {
        CartActions::recalculateTotal($cart);
    }
}
