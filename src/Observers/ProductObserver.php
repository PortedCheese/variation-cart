<?php

namespace PortedCheese\VariationCart\Observers;

use App\Cart;
use App\Product;
use App\ProductVariation;
use PortedCheese\VariationCart\Facades\CartActions;

class ProductObserver
{
    /**
     * Перед обновлением.
     *
     * @param Product $product
     */
    public function updated(Product $product)
    {
        $original = $product->getOriginal();
        // Если выключили вариацию, убрать ее из всех корзин.
        if (! empty($original["published_at"]) && empty($product->published_at)) {
            foreach ($product->variations as $variation) {
                /**
                 * @var ProductVariation $variation
                 */
                $carts = $variation->carts;
                $variation->carts()->sync([]);
                foreach ($carts as $cart) {
                    $this->changeCart($cart);
                }
            }
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
