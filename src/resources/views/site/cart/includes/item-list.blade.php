@foreach ($cartItems as $item)
    @php($border = ! $loop->last)
    <div id="variation-{{ $item->variation->id }}" class="row cart-item{{ $border ? " border-bottom" : "" }}">
        <div class="col-6 col-lg-2 cart-item__cover-image catalog-image">
            @if ($item->cover)
                @img([
                    "image" => $item->cover,
                    "template" => "product-show-thumb",
                    "lightbox" => "image-{$item->variation->id}",
                    "imgClass" => "img-fluid",
                    "grid" => [],
                ])
            @else
                <div class="catalog-image__empty cart-item__empty">
                    <svg class="catalog-image__empty-ico cart-item__empty-ico">
                        <use xlink:href="#catalog-empty-image"></use>
                    </svg>
                </div>
            @endif
        </div>
        <div class="col-6 col-lg-5">
            <a href="{{ route("catalog.products.show", ["product" => $item->product]) }}" class="cart-item__title">
                {{ $item->product->title }}
            </a>
            <div class="cart-item__description">{{ $item->variation->description }}</div>
            <div class="cart-item__actions">
                @include("category-product::site.products.includes.favorite", ["product" => $item->product])
                <form action="{{ route("catalog.cart.delete", ["variation" => $item->variation]) }}" method="post">
                    @csrf
                    @method("delete")
                    <button type="submit" class="btn btn-link cart-item__delete">
                        Удалить
                    </button>
                </form>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <cart-change-quantity :init-quantity="{{ $item->quantity }}"
                                  @if (config("variation-cart.showCartDiscount"))
                                  :show-discount="true"
                                  @endif
                                  :init-variation="{{ json_encode($item->variationData) }}"
                                  update-url="{{ route("catalog.cart.update", ["variation" => $item->variation]) }}">
            </cart-change-quantity>
        </div>
    </div>
@endforeach