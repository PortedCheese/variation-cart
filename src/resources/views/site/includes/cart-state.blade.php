@if (config("variation-cart.showCartIco"))
    <li class="nav-item">
        <cart-state cart-url="{{ route("catalog.cart.index") }}"
                    :cart-count="{{ $count }}"
                    :cart-total="{{ $total }}">
        </cart-state>
    </li>
@endif