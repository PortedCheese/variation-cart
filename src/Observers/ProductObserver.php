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
        // Если выключили товар, убрать вариации из всех корзин.
        if (! empty($original["published_at"]) && empty($product->published_at)) {
            foreach ($product->variations as $variation) {
                /**
                 * @var ProductVariation $variation
                 */
                // убрать дополнения
                $this->changeAddons($variation);

                // убрать все комплекты с ней
                $this->changeSets($variation);

                // убрать из корзин
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

    /**
     * Если вариация есть в дополнениях комлектов корзин
     *
     * @param ProductVariation $variation
     * @return void
     *
     */
    protected function changeAddons(ProductVariation $variation){
        // убрать дополнения
        $addons = $variation->addons;
        if (isset($addons)){
            foreach ($addons as $addon) {
                $set = $addon->set;
                $addon->delete();
                if ($set->addons()->count() < 1)
                    $set->delete();
                $this->changeCart($addon->cart);
            }
        }
    }

    /**
     * Если у вариации есть комплекты дополнений
     *
     * @param ProductVariation $variation
     * @return void
     */
    protected function changeSets(ProductVariation $variation){
        $sets =  $variation->sets;
        if (isset($sets)){
            foreach ($sets as $set) {
                foreach ($set->addons as $addon) {
                    $addon->delete();
                }
                $set->delete();
            }
        }
    }
}
