<?php

if (! function_exists("variation_cart")) {
    /**
     * @return \PortedCheese\VariationCart\Helpers\CartActionsManager
     */
    function variation_cart() {
        return app("cart-actions");
    }
}
