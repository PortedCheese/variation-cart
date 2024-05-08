@foreach ($cartItems as $item)
    @php($border = ! $loop->last)
    <div id="variation-{{ $item->variation->id }}" class="row cart-item{{ $border ? " border-bottom" : "" }}">
        <div class="col-6 col-sm-4 col-lg-2 cart-item__cover-image catalog-image order-1">
            @if ($item->cover)
                @img([
                    "image" => $item->cover,
                    "template" => "small",
                    "lightbox" => "image-{$item->variation->id}",
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
            <cart-change-quantity :init-quantity="{{ $item->quantity }}"
                                  @if (config("variation-cart.showCartDiscount"))
                                  :show-discount="true"
                                  @endif
                                  :init-variation="{{ json_encode($item->variationData) }}"
                                  update-url="{{ route("catalog.cart.update", ["variation" => $item->variation]) }}">
            </cart-change-quantity>
        </div>

        <div class="col-12 col-sm-8 col-lg-6 col-xl-5 order-3 order-sm-2">
            <a href="{{ route("catalog.products.show", ["product" => $item->product]) }}" class="cart-item__title">
                {{ $item->product->title }}
            </a>
            <div class="cart-item__description">{{ $item->variation->description }}</div>
            @isset ( $item->variation->specifications)
                <div class="cart-item__description">
                    @foreach($item->variation->specifications as $spec)
                        <small class="mr-2">{{ $spec->value }}</small>
                    @endforeach
                </div>
            @endisset
            <div class="cart-item__actions">
                @include("category-product::site.products.includes.favorite", ["product" => $item->product])
                <form action="{{ route("catalog.cart.delete", ["variation" => $item->variation]) }}" method="post">
                    @csrf
                    @method("delete")
                    <button type="submit" class="btn btn-link cart-item__delete">
                        <svg class="cart-item__ico">
                            <use xlink:href="#cart-trash"></use>
                        </svg>
                        Удалить
                    </button>
                </form>
            </div>
        </div>
    </div>
@endforeach