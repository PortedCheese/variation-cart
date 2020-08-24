@if (config("variation-cart.showCartIco"))
    <li class="nav-item">
        <cart-state cart-url="#" :cart-count="{{ $count }}" :cart-total="{{ $total }}"></cart-state>
    </li>
@endif