@if(isset($item->addons) && count($item->addons))
    <div class="row justify-content-end">
        <div class="col-12">
            <span class="text-secondary">Дополнения:</span>
        </div>
    </div>
    @foreach($item->addons as $addon)
        @php($border = $loop->last && ! $last)
        <div id="variation-{{ $addon->variation->id }}" class="row cart-item{{ $border ? " border-bottom" : "" }}">
            <div class="col-6 col-sm-4 col-lg-2 cart-item__cover-image catalog-image order-1">
                @if ($addon->variation->image)
                    @img([
                    "image" => $item->variation->image,
                    "template" => "small",
                    "lightbox" => "image-{$addon->variation->id}",
                    "imgClass" => "img-fluid rounded",
                    "grid" => [
                    "product-show-thumb" => 992,
                    ],
                    ])
                @elseif ($addon->cover)
                    @img([
                    "image" => $addon->variation->product->cover,
                    "template" => "small",
                    "lightbox" => "image-{$addon->variation->id}",
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
                <cart-change-quantity :init-quantity="{{ $addon->quantity }}"
                                      @if (config("variation-cart.showCartDiscount"))
                                      :show-discount="true"
                                      :show-quantity="true"
                                      @endif
                                      :init-set="{{ $addon->set }}"
                                      :init-addon="{{ $addon->addonId }}"
                                      :init-variation="{{ json_encode($addon->variationData) }}"
                                      update-url="{{ route("catalog.cart.update-addon", ["variation" => $addon->variation]) }}">
                </cart-change-quantity>
            </div>
            <div class="col-12 col-sm-8 col-lg-6 col-xl-5 order-3 order-sm-2">
                <div class="cart-item__title">
                    {{ $addon->product->title }}
                </div>
                <div class="cart-item__description">{{ $addon->variation->description }}</div>
                @isset ( $addon->variation->specifications)
                    <div class="cart-item__description">
                        @foreach($addon->variation->specifications as $spec)
                            <small class="mr-2">{{ $spec->value }}</small>
                        @endforeach
                    </div>
                @endisset
                <div class="cart-item__actions">
                    @includeIf("variation-cart::site.cart.includes.item-remove", ["item" => $addon, "addonId" => $addon->addonId, "text" => ""])
                </div>
            </div>
            @if ($loop->last)
                <div class="col-12 text-right order-last mt-3">
                    @includeIf("variation-cart::site.cart.includes.item-remove", ["item" => $item, "setId" => $addon->set ,"text" => "Удалить комплект"])
                </div>
            @endif
        </div>
    @endforeach
@endif