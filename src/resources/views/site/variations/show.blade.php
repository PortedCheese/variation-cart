<add-to-cart :variations="{{ json_encode(product_variation()->getVariationsByProduct($product)) }}"
             cart-url="{{ route("catalog.cart.index") }}">
</add-to-cart>