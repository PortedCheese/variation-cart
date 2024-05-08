<div class="card cart-info">
    <div class="card-header cart-info__header">
        <h5 class="card-title cart-info__title-cover">
            <span class="cart-info__title">Ваш заказ</span>
        </h5>
    </div>
    <div class="card-body cart-info__body">
        @foreach ($cartItems as $item)
            <div class="cart-info__item">
                <a href="{{ route("catalog.products.show", ["product" => $item->product]) }}" class="cart-info__item-title">
                    {{ $item->product->title }}
                </a>
                <div class="cart-info__item-description">
                    {{ $item->variation->description }}
                </div>
                @isset ($item->variation->specifications)
                    <div class="cart-info__item-description">
                        @foreach($item->variation->specifications as $spec)
                            <small class="mr-2">{{ $spec->value }}</small>
                        @endforeach
                    </div>
                @endisset

                <div class="cart-info__item-price rub-format">
                                <span class="rub-format__value">
                                    <span class="cart-info__item-quantity">{{ $item->quantity }} {{ $item->variation->short_measurement }} x</span>
                                    {{ $item->variation->price }}
                                </span>
                    <svg class="rub-format__ico cart-info__discount-ico cart-info__discount-ico_big">
                        <use xlink:href="#catalog-rub"></use>
                    </svg>
                </div>
            </div>
        @endforeach

        <hr>

        <div class="cart-info__cost-item cart-info__cost-item_total">
            <span class="cart-info__cost-title">Стоимость</span>
            <span class="rub-format cart-info__cost-value">
                <span class="rub-format__value">
                    {{ $cart->humanTotal }}
                </span>
                <svg class="rub-format__ico cart-info__discount-ico cart-info__discount-ico_big">
                    <use xlink:href="#catalog-rub"></use>
                </svg>
            </span>
        </div>
    </div>
    <div class="card-footer cart-info__footer">
        <button type="submit" form="checkout-order-form" class="btn btn-primary btn-block">Оформить заказ</button>
    </div>
</div>