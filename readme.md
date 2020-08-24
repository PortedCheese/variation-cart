# Variation Cart

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