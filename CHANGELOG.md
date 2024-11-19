##Versions

###v3.0.2-v3.0.3 Fix Add to cart
Правка компонента AddVariationsToCart:
- не показывать вообще кнопку купить, если не выбрана ни одна вариация
- не показывать модальное  окно с допами, если допов нет и если не выбрана вариация
- не показывать input с выбором количества товара, если добавлено хотя бы одно дополнение (по умолчанию в комплект обаляется только один основной товар)
- отдельно выводить количество товаров текстом, если выбрано хотя бы одно дополнение

Обновление:

        php artisan vendor:publish --provider="PortedCheese\VariationCart\ServiceProvider" --tag=public --force
###v3.0.0-v3.0.1 Base5 & Bootstrap 5
Правки шаблонов и компонентов под BS5

Обновление:

        php artisan vendor:publish --provider="PortedCheese\VariationCart\ServiceProvider" --tag=public --force

Проверить переопределение:
- компонентов > AddVariationsToCart, CartSate, ChangeItemQuantity
- стилей
- фасада CartActionsManager: mergeCarts
  
###v2.0.0 - 2.0.2: Cart Sets & SetAddons
- Комплекты Товар + Дополнения в корзине
- Модальное окно с Дополнениями
- Удалить дополнение или комплект из корзины

Добавлено:
- миграция, модель: CartProductVariationSet, CartProductVariationSetAddon
- компоненты: AddonsModalComponent, ChangeAddonQuantityCpmponent
- шаблоны: site.cart,includes.addons-list & item-remove
- CartActions > changeAddonQuantiity, deleteSet, deleteAddonItem
- Controllers/Site/CartCOntroller > deleteAddonItem, DeleteSet, ChangeAddonQuantity, changeQuantityValidator
- Models/Cart > sets(), reserveCount, aloneCount
- 
  Обновление:
        
        php artisan migrate
        php artisan make:variation-cart --models
        php artisan vendor:publish --provider="PortedCheese\VariationCart\ServiceProvider" --tag=public --force

Проверить переопрделение:
- CartActions > getCartItems (+addons),  addToCart (+addons), changeQuantity, deleteItem, clearCart, makeOrder, recalculateTotal
- Controllers/Site/CartCOntroller > completePage, addToCart, addToCartValidator
- Models/Cart > getSaleLessAttribute, getDiscountAttribute
- CartObserver > deleting
- ProductObserver > updated
- ProductVariationObserver > updated, deleted
- Компоненты > AddVariationsToCart (addons, Купить комплект, модальное окно допов, шина событий), ChangeItemQuantity ( правки для допов без количества, шина), CartState & CartInfo (шина)
- Scss > cart-info, cart-item, product0add-to-cart,
- Blades: site.cart.complete, cart.includes.checkout-info, cart,includes.item-list

###v1.3: variation specifications & image (product-variation ^1.3)
Обновление

    php artisan vendor:publish --provider="PortedCheese\VariationCart\ServiceProvider" --tag=public --force
    npm run
    php artisan cache:clear
    php artisan queue:restart

Проверить переопределение:

- Helpers: CartActionManager > specifications
- components: AddVariationsToCartComponent > specifications
- blades: site.variations.show, site.cart.complete, site.cart.includes.checkout-info, site.cart.includes.item-list

###v1.2: measurement (product-variation ^1.2)
Обновление:
        
    php artisan vendor:publish --provider="PortedCheese\VariationCart\ServiceProvider" --tag=public --force

Проверить переопределение:
- components: ChangeItemQuantity
- blades: site.cart.complete, site.cart.includes.checkout-info
        
###v1.1.2: 
- fix - работы с корзиной из-под одного аккаунта в нескольких браузерах (забывать куку и не передавать корзину при выходе)

Проверить переопредление 
- CartActionsManager методов: recalculateTotal(), findCartByCookie(), checkUserAuthCart()

###v1.1.1: иземенен вывод отключенных вариаций 
Вывод отключенных вариаций без выбора количества, vendorName

Проверить переопределение:
- компонент AddVariationsToCart

Обновление:

         php artisan vendor:publish --provider="PortedCheese\VariationCart\ServiceProvider" --tag=public --force
###v1.0.3: Добавлен класс в конфиг productVariationResource

###v1.0.2: Добавлен наблюдатель для товара
Обновление:

         php artisan make:variation-cart --observers