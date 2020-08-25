@extends("layouts.boot")

@section('page-title', "Корзина")

@section("header-title", "Корзина")

@section("contents")
    <div class="row">
        @if ($cart)
        <div class="col-12 col-md-8 col-lg-9 mb-3">
            <div class="card">
                <div class="card-body">
                    @include("variation-cart::site.cart.includes.item-list")
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <cart-info :init-cart="{{ json_encode($cart) }}"
                       checkout-url="{{ route("catalog.cart.checkout") }}"
                       @if (config("variation-cart.showCartDiscount"))
                       :show-discount="true"
                       @endif>
            </cart-info>
        </div>
        @else
            <div class="col-12">
                <p class="lead">
                    Корзина пуста, <a href="{{ route("catalog.categories.index") }}">начать покупки</a>
                </p>
            </div>
        @endif
    </div>
@endsection