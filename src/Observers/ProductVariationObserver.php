<?php

namespace PortedCheese\VariationCart\Observers;

use App\ProductVariation;
use PortedCheese\VariationCart\Facades\CartActions;

class ProductVariationObserver
{
    /**
     * Перед обновлением.
     *
     * @param ProductVariation $variation
     */
    public function updating(ProductVariation $variation)
    {
        $original = $variation->getOriginal();
        // Если выключили вариацию.
        if (empty($original["disabled_at"]) && ! empty($variation->disabled_at)) {
            $carts = $variation->carts;
            $variation->carts()->sync([]);
            foreach ($carts as $cart) {
                CartActions::recalculateTotal($cart);
            }
        }
    }

    /**
     * После удаления.
     *
     * @param ProductVariation $variation
     */
    public function deleted(ProductVariation $variation)
    {
        $variation->carts()->sync([]);
    }
}
