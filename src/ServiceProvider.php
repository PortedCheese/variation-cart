<?php

namespace PortedCheese\VariationCart;

use App\Cart;
use App\Observers\Vendor\VariationCart\CartObserver;
use App\Observers\Vendor\VariationCart\ProductVariationObserver;
use App\ProductVariation;
use PortedCheese\VariationCart\Console\Commands\VariationCartMakeCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        // Подключение конфигурации.
        $this->publishes([
            __DIR__ . "/config/variation-cart.php" => config_path("variation-cart")
        ], "config");

        // Подключение миграций.
        $this->loadMigrationsFrom(__DIR__ . "/database/migrations");

        // Console.
        if ($this->app->runningInConsole()) {
            $this->commands([
                VariationCartMakeCommand::class,
            ]);
        }

        // Подключение путей.
        $this->addRoutes();

        // Подключение шаблонов.
        $this->loadViewsFrom(__DIR__ . "/resources/views", "variation-cart");

        // Assets.
        $this->publishes([
            __DIR__ . "/resources/js/components" => resource_path("js/components/vendor/variation-cart")
        ], "public");

        // Наблюдатели.
        $this->addObservers();
    }

    public function register()
    {
        // Стандартная конфигурация.
        $this->mergeConfigFrom(
            __DIR__ . "/config/variation-cart.php", "variation-cart"
        );

        // Facades.
        $this->initFacades();
    }

    /**
     * Добавление путей.
     */
    protected function addRoutes()
    {
        $this->addSiteRoutes();
    }

    protected function addSiteRoutes()
    {
        // Корзина.
        if (config("variation-cart.cartSiteRoutes")) {
            $this->loadRoutesFrom(__DIR__ . "/routes/site/cart.php");
        }
    }

    /**
     * Подключение Facade.
     */
    protected function initFacades()
    {
        $this->app->singleton("cart-actions", function () {
            $class = config("variation-cart.cartFacade");
            return new $class;
        });
    }

    /**
     * Наблюдатели.
     */
    protected function addObservers()
    {
        if (class_exists(CartObserver::class) && class_exists(Cart::class)) {
            Cart::observe(CartObserver::class);
        }

        if (class_exists(ProductVariationObserver::class) && class_exists(ProductVariation::class)) {
            ProductVariation::observe(ProductVariationObserver::class);
        }
    }
}
