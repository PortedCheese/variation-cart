# Variation Cart

Добавление корзины товаров в каталог

## Install
    php artisan migrate
    
    php artisan vendor:publish --provider="PortedCheese\VariationCart\ServiceProvider" --tag=public --force
    
    php artisan make:variation-cart
                                {--all : Run all}
                                {--models : Export models}
                                {--observers : Export observers}
                                {--controllers : Export controllers}
                                {--policies : Export and create rules}
                                {--only-default : Create default rules}
                                {--scss : Export scss}
                                {--vue : Export vue}
                                
## Config

Выгрузить конфигурацию:

    php artisan vendor:publish --provider="PortedCheese\VariationCart\ServiceProvider" --tag=config
     
Переменные конфигурации:

    cartFacade - Класс фасада для корзины
    cartSiteRoutes(true) - Использовать роуты для сайта из пакета
    showCartIco(true) - Показать иконку корзины
    showCartDiscount(true) - Показывать скидки в корзине
    enableCart(true) - Включить корзину
    
### Versions
    v1.1.1: 
        - иземенен вывод отключенных вариаций (без выбора количества), компонент AddVariationsToCart
        - vendorName
        Обновление:
        - php artisan vendor:publish --provider="PortedCheese\VariationCart\ServiceProvider" --tag=public --force
    v1.0.3:
        - Добавлен класс в конфиг productVariationResource
    v1.0.2:
        - Добавлен наблюдатель для товара
    Обновление:
        - php artisan make:variation-cart --observers