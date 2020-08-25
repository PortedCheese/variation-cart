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
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Ваша корзина</h5>
                </div>
                <div class="card-body">
                    Info
                </div>
                <div class="card-footer">
                    <a href="#" class="btn btn-primary btn-block">Перейти к оформлению</a>
                </div>
            </div>
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