@extends("layouts.boot")

@section('page-title', "Спасибо за оформление заказа")

@section("header-title", "Спасибо за оформление заказа")

@section("contents")
    <div class="row complete-page">
        <div class="col-12 col-mb-8 mt-3">
            <div class="complete-page__result">
                <span class="rub-format cart-info__cost-value">
                    <span class="rub-format__value complete-page__text">
                        Ваш заказ №&nbsp;{{ $order->number }} от {{ $order->created_human_date }} на сумму: <span class="complete-page__total">{{ $order->human_total }}</span>
                    </span>
                    <svg class="rub-format__ico complete-page__ico">
                        <use xlink:href="#catalog-rub-bold"></use>
                    </svg>
                </span>
            </div>
            <p>В ближайшее время с Вами свяжутся</p>
        </div>
        <div class="col-12 col-md-8 mb-3">
            <div class="card">
                <div class="card-body">
                    @foreach ($items as $item)
                        @php($border = ! $loop->last)
                        <div class="row cart-item{{ $border ? " border-bottom" : "" }}">
                            <div class="col-6 col-sm-4 col-lg-2 cart-item__cover-image catalog-image order-1">
                                @if ($item->product->cover)
                                    @pic([
                                        "image" => $item->product->cover,
                                        "template" => "small",
                                        "imgClass" => "img-fluid rounded",
                                        "grid" => [
                                            "product-show-thumb" => 992,
                                        ],
                                    ])
                                @else
                                    <div class="catalog-image__empty cart-item__empty">
                                        <svg class="catalog-image__empty-ico cart-item__empty-ico">
                                            <use xlink:href="#catalog-empty-image"></use>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="col-6 col-sm-12 col-lg-4 col-xl-5 order-2 order-sm-3">
                                <div class="cart-item__form-info complete-page__info">
                                    <div class="rub-format cart-item__form-price">
                                        <span class="rub-format__value">
                                            {{ $item->human_price }}
                                        </span>
                                        <svg class="rub-format__ico cart-item__rub">
                                            <use xlink:href="#catalog-rub"></use>
                                        </svg>
                                    </div>
                                    <div class="cart-item__form-quantity complete-page__quantity">
                                        {{ $item->quantity }} {{ $item->short_measurement }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-8 col-lg-6 col-xl-5 order-3 order-sm-2">
                                <a href="{{ route("catalog.products.show", ["product" => $item->product]) }}" class="cart-item__title">
                                    {{ $item->product->title }}
                                </a>
                                <div class="cart-item__description">{{ $item->description }}</div>
                                <div class="cart-item__actions">
                                    @include("category-product::site.products.includes.favorite", ["product" => $item->product])
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <a href="{{ route("catalog.categories.index") }}" class="btn btn-primary mt-3">Начать новые покупки</a>
        </div>
    </div>
@endsection