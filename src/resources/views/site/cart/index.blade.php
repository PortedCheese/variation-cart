@extends("layouts.boot")

@section('page-title', "Корзина")

@section("header-title", "Корзина")

@section("contents")
    <div class="row">
        @if ($cart)
        <div class="col-12 col-md-8 col-lg-9 mb-3">
            <div class="card">
                <div class="card-body">
                    @foreach ($cart as $item)
                        @php($border = ! $loop->last)
                        <div id="variation-{{ $item->variation->id }}" class="row{{ $border ? " border-bottom" : "" }}">
                            <div class="col-12">
                                @if ($item->cover)
                                    @img([
                                        "image" => $item->cover,
                                        "template" => "small",
                                        "lightbox" => "image-{$item->variation->id}",
                                        "imgClass" => "img-fluid",
                                        "grid" => [],
                                    ])
                                @endif
                                <a href="{{ route("catalog.products.show", ["product" => $item->product]) }}">
                                    {{ $item->product->title }}
                                </a>
                                <div>{{ $item->variation->description }}</div>
                                <div>
                                    <form action="{{ route("catalog.cart.delete", ["variation" => $item->variation]) }}" method="post">
                                        @csrf
                                        @method("delete")
                                        <button type="submit" class="btn btn-link">Удалить</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
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