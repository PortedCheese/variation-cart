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
        // Если выключили вариацию
        if (empty($original["disabled_at"]) && ! empty($variation->disabled_at)) {
            // убрать дополнения
            $this->changeAddons($variation);

            // убрать все комплекты с ней
            $this->changeSets($variation);

            // убрать ее из всех корзин.
            $carts = $variation->carts;
            $variation->carts()->sync([]);
            foreach ($carts as $cart) {
                $this->changeCart($cart);
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
        // убрать дополнения
        $this->changeAddons($variation);

        // убрать все комплекты с ней
        $this->changeSets($variation);

        $carts = $variation->carts;
        // убрать из корзин
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
