<?php

namespace PortedCheese\VariationCart\Console\Commands;

use PortedCheese\BaseSettings\Console\Commands\BaseConfigModelCommand;

class VariationCartMakeCommand extends BaseConfigModelCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "make:variation-cart
                    {--all : Run all}
                    {--models : Export models}
                    {--controllers : Export controllers}
                    {--observers : Export observers}
                    {--policies : Export and create rules}
                    {--only-default : Create only default rules}
                    {--scss : Export scss}
                    {--vue : Export vue}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Settings for variations cart";

    /**
     * Имя пакета.
     *
     * @var string
     */
    protected $vendorName = 'PortedCheese';
    protected $packageName = "VariationCart";

    /**
     * The models that need to be exported.
     * @var array
     */
    protected $models = [
        "Cart",
        "CartProductVariationSet",
        "CartProductVariationSetAddon"
    ];

    /**
     * Создание контроллеров.
     *
     * @var array
     */
    protected $controllers = [
        "Admin" => [],
        "Site" => [
            "CartController",
        ],
    ];

    /**
     * Создание наблюдателей
     *
     * @var array
     */
    protected $observers = ["CartObserver", "ProductVariationObserver", "ProductObserver"];

    /**
     * Папка для vue файлов.
     *
     * @var string
     */
    protected $vueFolder = "variation-cart";

    /**
     * Список vue файлов.
     *
     * @var array
     */
    protected $vueIncludes = [
        'admin' => [],
        'app' => [
            "add-to-cart" => "AddVariationsToCartComponent",
            "cart-state" => "CartStateComponent",
            "cart-change-quantity" => "ChangeItemQuantityComponent",
            "cart-info" => "CartInfoComponent",
            "add-to-cart-button" => "ProductTeaserAddToCartComponent",
        ],
    ];

    /**
     * Политики.
     *
     * @var array
     */
    protected $ruleRules = [
//        [
//            "title" => "Корзины",
//            "slug" => "variation-cart",
//            "policy" => "CartPolicy",
//        ]
    ];

    /**
     * Стили.
     * 
     * @var array 
     */
    protected $scssIncludes = [
        "app" => [
            "variation-cart/cart-state",
            "variation-cart/choose-quantity",
            "variation-cart/product-add-to-cart",
            "variation-cart/cart-item",
            "variation-cart/cart-info",
            "variation-cart/complete-page",
        ],
        "admin" => [],
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $all = $this->option("all");

        if ($this->option("models") || $all) {
            $this->exportModels();
        }

        if ($this->option("controllers") || $all) {
//            $this->exportControllers("Admin");
            $this->exportControllers("Site");
        }

        if ($this->option("observers") || $all) {
            $this->exportObservers();
        }

        if ($this->option("vue") || $all) {
            $this->makeVueIncludes("admin");
            $this->makeVueIncludes("app");
        }

        if ($this->option("policies") || $all) {
            $this->makeRules();
        }

        if ($this->option("scss") || $all) {
            $this->makeScssIncludes("app");
//            $this->makeScssIncludes("admin");
        }
    }
}