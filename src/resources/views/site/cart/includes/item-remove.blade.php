<form action="{{ isset($addonId) ? route("catalog.cart.delete-addon", ["addon" => $addonId]) :
( isset($setId) ? route("catalog.cart.delete-set", ["set" => $setId]) :
route("catalog.cart.delete", ["variation" => $item->variation])) }}"
      method="post">
    @csrf
    @method("delete")
    <button type="submit" class="btn btn-link cart-item__delete{{ isset($text) ? " pl-0" : "" }}">
        <svg class="cart-item__ico">
            <use xlink:href="#cart-trash"></use>
        </svg>
        {{ isset($text)? $text: "Удалить" }}
    </button>
</form>