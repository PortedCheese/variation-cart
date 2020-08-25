@foreach ($cartItems as $item)
    @php($border = ! $loop->last)
    <div id="variation-{{ $item->variation->id }}" class="row py-3{{ $border ? " border-bottom" : "" }}">
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
            <div>
                <cart-change-quantity :init-quantity="{{ $item->quantity }}"
                                      @if (config("variation-cart.showCartDiscount"))
                                      :show-discount="true"
                                      @endif
                                      :init-variation="{{ json_encode($item->variationData) }}"
                                      update-url="{{ route("catalog.cart.update", ["variation" => $item->variation]) }}">
                </cart-change-quantity>
            </div>
        </div>
    </div>
@endforeach