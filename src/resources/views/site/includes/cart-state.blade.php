@if (config("variation-cart.showCartIco"))
    <li class="nav-item">
        <cart-state cart-url="{{ route("catalog.cart.index") }}"
                    :cart-data="{{ json_encode($cartInfo) }}">
        </cart-state>
    </li>
@endif