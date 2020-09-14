@extends("layouts.boot")

@section('page-title', "Оформление заказа")

@section("header-title", "Оформление заказа")

@section("contents")
    <div class="row">
        <div class="col-12 col-md-8 mb-3">
            @include("variation-cart::site.cart.includes.checkout-form")
        </div>
        <div class="col-12 col-md-4">
            @include("variation-cart::site.cart.includes.checkout-info")
        </div>
    </div>
@endsection