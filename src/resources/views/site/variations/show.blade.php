<add-to-cart :variations="{{ json_encode(product_variation()->getVariationsByProduct($product)) }}"
             cart-url="{{ route("catalog.cart.index") }}"
             :specifications="{{ json_encode(product_variation()->getVariationsSpecificationsByProduct($product),JSON_FORCE_OBJECT) }}"
>
</add-to-cart>