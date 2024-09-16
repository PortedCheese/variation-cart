<?php

namespace PortedCheese\VariationCart\Helpers;

use App\Cart;
use App\CartProductVariationSet;
use App\CartProductVariationSetAddon;
use App\Order;
use App\ProductVariation;
use Illuminate\Support\Facades\Cache;
use PortedCheese\ProductVariation\Events\CreateNewOrder;
use PortedCheese\ProductVariation\Facades\OrderActions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartActionsManager
{
    /**
     * @return Cart
     */
    public function initCart()
    {
        $cart = $this->getCart();
        if ($cart) return $cart;
        return Cart::create([]);
    }

    /**
     * Найти корзину.
     *
     * @return Cart|bool
     */
    public function getCart()
    {
        $cart = $this->findCartByCookie();
        if ($cart) return $cart;
        return $this->findCartByAuth();
    }

    /**
     * Поставить куку.
     *
     * @param Cart $cart
     */
    public function setCookie(Cart $cart)
    {
        $cookie = Cookie::make("cartUuid", $cart->uuid, 60*24*30);
        Cookie::queue($cookie);
    }

    /**
     * Получить краткую информацию о корзине.
     *
     * @param Cart|null $cart
     * @return object
     */
    public function getCartInfo(Cart $cart = null)
    {
        if (empty($cart)) {
            $cart = $this->getCart();
        }
        if ($cart) {
            $key = "cartInfo:{$cart->id}";
            $data = Cache::rememberForever($key, function () use ($cart) {
                return [
                    "total" => (float) $cart->total,
                    "humanTotal" => $cart->human_total,
                    "count" => $cart->count,
                    "saleLess" => $cart->sale_less,
                    "humanSaleLess" => $cart->human_sale_less,
                    "discount" => $cart->discount,
                    "humanDiscount" => $cart->human_discount,
                ];
            });
        }
        else {
            $data = [
                "total" => 0,
                "humanTotal" => 0,
                "count" => 0,
                "saleLess" => 0,
                "humanSaleLess" => 0,
                "discount" => 0,
                "humanDiscount" => 0,
            ];
        }
        return (object) $data;
    }

    /**
     * Позиции корзины.
     *
     * @param Cart|null $cart
     * @return array|bool
     */
    public function getCartItems(Cart $cart = null)
    {
        if (empty($cart)) {
            $cart = $this->getCart();
        }
        if (! $cart) {
            return false;
        }
        $items = [];
        $collection = $cart->variations()
            ->join("products", "product_variations.product_id", "products.id")
            ->with("product", "product.cover")
            ->orderBy("price")
            ->orderBy("products.title")
            ->get();
        $class = config("product-variation.productVariationResource");
        // addons
        $sets = $cart->sets()->get();
        $addons = [];
        $count = [];
        foreach ($sets as $set) {
            $setItems = $set->addons()->get();
            $setItemsArray = [];
            foreach ($setItems as $setItem) {
                $setItemsArray []= (object) [
                    "cover" => $setItem->variation->product->cover,
                    "product" => $setItem->variation->product,
                    "title" => $setItem->variation->product->title,
                    "variation" => $setItem->variation,
                    "variationData" => new $class($setItem->variation),
                    "quantity" => $setItem->quantity,
                    "specifications" => $setItem->variation->specificationsArray,
                    "addons" => null,
                    "set" => $set->id,
                    "addonId" => $setItem->id,
                ];
            }
            $addons[$set->product_variation_id][] = $setItemsArray;
        }
        // variations with addons
        foreach ($collection as $variation) {
            $count[$variation->id] = isset($addons[$variation->id])? count($addons[$variation->id]) : 0;
            $product = $variation->product;
            $pivot = $variation->pivot;
            if ($count[$variation->id] <= $pivot->quantity){
                if (isset($addons[$variation->id])){
                    foreach ($addons[$variation->id] as $addonItems){
                        $items[] = (object) [
                            "cover" => $product->cover,
                            "product" => $product,
                            "title" => $product->title,
                            "variation" => $variation,
                            "variationData" => new $class($variation),
                            "quantity" => 1,
                            "specifications" => $variation->specificationsArray,
                            "addons" => $addonItems
                        ];
                    }
                }
            }
            // variations
            $q = $pivot->quantity - $count[$variation->id];
            if ($q > 0){
                $items[] = (object) [
                    "cover" => $product->cover,
                    "product" => $product,
                    "title" => $product->title,
                    "variation" => $variation,
                    "variationData" => new $class($variation),
                    "quantity" => $q,
                    "specifications" => $variation->specificationsArray,
                    "addons" => []
                ];
            }
        }

        return $items;
    }

    /**
     * Добавить в корзину.
     *
     * @param ProductVariation $variation
     * @param int $quantity
     * @param array|null $addons
     * @param Cart|null $customCart
     * @return Cart
     */
    public function addToCart(ProductVariation $variation, $quantity = 1, Array $addons = null, Cart $customCart = null)
    {
        $cart = $customCart ?? $this->initCart();
        // Если вариация выключена, вернуть корзину без изменения.
        if ($variation->disabled_at) {
            session()->flash("addToCartResult", [
                "success" => false,
                "message" => "Товар закончился",
            ]);
            return $cart;
        }
        // если дополнения выключены - вернуть корзину без изменения.
        if (isset($addons)){
            foreach ($addons as $addon){
                $addonVariation = ProductVariation::query()->find($addon["id"]);
                if (! isset($addonVariation) ||  $addonVariation->disabled_at){
                    session()->flash("addToCartResult", [
                        "success" => false,
                        "message" => "Дополнение закончилось",
                    ]);
                    return $cart;
                }
            }
        }

        $oldQuantity = DB::table("cart_product_variation")
            ->select("quantity")
            ->where("cart_id", $cart->id)
            ->where("product_variation_id", $variation->id)
            ->first();
        if ($oldQuantity) {
            $quantity += $oldQuantity->quantity;
        }
        if (isset($addons))
        {
            $this->addonsToCart($cart, $variation, $addons );
        }
        $cart->variations()->syncWithoutDetaching([
            $variation->id => ["quantity" => $quantity]
        ]);
        $this->recalculateTotal($cart);
        return $cart;
    }

    /**
     * Допы к товару
     *
     * @param Cart $cart
     * @param ProductVariation $variation
     * @param array $addons
     * @return void
     */
    protected function addonsToCart(Cart $cart, ProductVariation $variation, Array $addons){
        if (! count($addons)) return;

        $addonSet = CartProductVariationSet::create([
            'product_variation_id' => $variation->id,
            'cart_id' => $cart->id,
        ]);
        foreach ($addons as $addon){
            $addonSet->addons()->create([
                "product_variation_id" => $addon["id"],
                "cart_id" => $cart->id,
                "quantity" => $addon["quantity"]
            ]);
        }
    }

    /**
     * Изменить количество.
     *
     * @param ProductVariation $variation
     * @param int $quantity
     * @param Cart|null $customCart
     * @return Cart|bool
     */
    public function changeQuantity(ProductVariation $variation, $quantity = 1, Cart $customCart = null)
    {
        $cart = $customCart ?? $this->initCart();
        if (! $cart) {
            session()->flash("changeQuantityResult", [
                "success" => false,
                "message" => "Корзина не найдена",
            ]);
            return $cart;
        }
        $reserveCount = $cart->reserveCount($variation);
        $cart->variations()->syncWithoutDetaching([
            $variation->id => ["quantity" => $reserveCount+$quantity]
        ]);
        $this->recalculateTotal($cart);
        return $cart;
    }

    /**
     * Изменить количество дополнения
     *
     * @param $quantity
     * @param $setId
     * @param $addonId
     * @param Cart|null $customCart
     * @return Cart|bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function changeAddonQuantity($quantity = 1, $setId = 0, $addonId = 0, Cart $customCart = null)
    {
        $cart = $customCart ?? $this->initCart();
        $set = CartProductVariationSet::query()->find($setId);
        $addon = CartProductVariationSetAddon::query()->find($addonId);
        if (! $cart || ! $set || ! $addon) {
            session()->flash("changeQuantityResult", [
                "success" => false,
                "message" => "Что-то пошло не так",
            ]);
            return $cart;
        }
        $addon->quantity = $quantity;
        $addon->save();
        $this->recalculateTotal($cart);
        $this->clearCartCache($cart);
        return $cart;
    }

    /**
     * Удалить товар из корзины.
     *
     * @param ProductVariation $variation
     */
    public function deleteItem(ProductVariation $variation)
    {
        $cart = $this->getCart();
        if (! $cart) {
            return;
        }

        $reserveCount = $cart->reserveCount($variation);
        if ($reserveCount === 0)
            $cart->variations()->detach($variation);
        else{
            $cart->variations()->syncWithoutDetaching([
                $variation->id => ["quantity" => $reserveCount]
            ]);
        }

        $this->recalculateTotal($cart);
    }

    /**
     * Удалить дополнение из корзины.
     *
     * @param CartProductVariationSetAddon $addon
     * @return void
     *
     */
    public function deleteAddonItem(CartProductVariationSetAddon $addon)
    {
        $cart = $this->getCart();
        if (! $cart) {
            return;
        }
        $set = $addon->set;
        if (! $set) {
            return;
        }
        $addon->delete();
        if (count($set->addons) < 1)
            $set->delete();

        $this->recalculateTotal($cart);
        $this->clearCartCache($cart);
    }

    /**
     * Удалить комплект из корзины.
     *
     * @param CartProductVariationSet $set
     * @return void
     *
     */
    public function deleteSet(CartProductVariationSet $set)
    {
        $cart = $this->getCart();
        if (! $cart) {
            return;
        }

        $variation = $set->variation;
        if (! $variation) return;

        $reserveCount = $cart->reserveCount($variation);
        if (! $reserveCount) return;

        $count = $cart->aloneCount($variation);

        // удалить допы
        foreach ($set->addons()->get() as $item) {
            $set->addons()->delete($item);
        }
        // отвязать вариацию
        switch ($count) {
            case 0 :
                break;
            case 1 :
                $cart->variations()->detach($variation);
                break;
            default:
                $cart->variations()->syncWithoutDetaching([$variation->id => ["quantity" => $count-1]]);
        }
        // удалить комплект
        $set->delete();

        $this->recalculateTotal($cart);
    }

    /**
     * Очистить корзину.
     *
     * @param Cart $cart
     */
    public function clearCart(Cart $cart)
    {
        $sets = $cart->sets;
        if (isset($sets)){
            foreach ($sets as $set){
                $addons = $set->addons;
                foreach ($addons as $addon) {
                    $addon->delete();
                }
                $set->delete();
            }
        }
        foreach ($cart->variations as $variation) {
            $cart->variations()->detach($variation);
        }
        $this->recalculateTotal($cart);
    }

    /**
     * Создать заказ.
     *
     * @param Cart $cart
     * @param array $userData
     * @return mixed
     */
    public function makeOrder(Cart $cart, array $userData)
    {
        $order = Order::create([
            "user_data" => $userData,
        ]);
        $items = $this->getCartItems($cart);
        OrderActions::makeOrderFromCart($order, $items);
        //Log::info(json_encode($items ));

//        foreach ($items as $item) {
//            $variations = [];
//            $addonsSets = [];
//            $variations[$item->variation->id] = $item->quantity;
//            OrderActions::addVariationsToOrder($order, $variations);
//            if ($addons = $item->addons){
//                foreach ($addons as $addon){
//                    $set = $addon->set;
//                    $addonsSets[$item->variation->id][$set][$addon->variation->id] = $addon->quantity;
//                }
//            }
//            OrderActions::addAddonVariationSetsToOrder($order,$addonsSets);
//        }
//        Log::info(json_encode($variations ));
//        Log::info(json_encode($addonsSets ));
//        foreach ($variations as $variationId => $variationGroup){
//            foreach ($variationGroup as $variationQuantity) {
//                OrderActions::addVariationsToOrder($order, $variations);
//            }
//        }


        $this->clearCart($cart);
        event(new CreateNewOrder($order));
        return $order;
    }

    /**
     * Пересчитать корзину.
     *
     * @param Cart $cart
     */
    public function recalculateTotal(Cart $cart)
    {
        $total = 0;
        foreach ($cart->variations()->get() as $variation) {
            $pivot = $variation->pivot;
            $total += $variation->price * $pivot->quantity;
        }
        // дополнения
        foreach ($cart->sets()->get() as $set){
            $addonVariations = $set->addons()->get();
            foreach ($addonVariations as $addon){
                $quantity = $addon->quantity;
                $total += $addon->variation->price * $quantity;
            }
        }
        $cart->total = $total;
        $cart->save();
    }

    /**
     * Очистить кэш корзины.
     *
     * @param Cart $cart
     */
    public function clearCartCache(Cart $cart)
    {
        $uuid = $cart->uuid;
        Cache::forget("cartByUuid:{$uuid}");

        $userId = $cart->user_id;
        if (! empty($userId)) {
            Cache::forget("cartByUserId:{$userId}");
        }

        $cartId = $cart->id;
        Cache::forget("cartInfo:{$cartId}");
    }

    /**
     * @return bool|Cart
     */
    protected function findCartByAuth()
    {
        if (! Auth::check()) return false;
        return $this->findCartByUserId(Auth::id());
    }

    /**
     * Найти корзину по куке.
     *
     * @return Cart|bool
     */
    protected function findCartByCookie()
    {
        $cookie = Cookie::get("cartUuid", false);
        if (! $cookie) return false;
        $cart = $this->findCartByUuid($cookie);
        if (! $cart) return false;
        if ($this->checkUserAuthCart($cart) == -1) return false;
        return $cart;
    }

    /**
     * @return void
     */
    public function forgetCookie()
    {
        $cookie = Cookie::forget("cartUuid");
        Cookie::queue($cookie);
    }

    /**
     * Если пользователь авторизован.
     *
     * @param Cart $cart
     * @return int|void
     */
    protected function checkUserAuthCart(Cart $cart)
    {
        if (! Auth::check() && ! empty($cart->user_id)) {
            $this->forgetCookie();
            // не передавать корзину сразу после выхода пользователя
            return -1;
        }
        if (! Auth::check()) return;
        if (! empty($cart->user_id)) return;
        $userCart = $this->findCartByUserId(Auth::id());
        // Если у пользователя была корзина,
        // нужно обеденить корзины
        if ($userCart && $userCart->id != $cart->id) {
            $this->mergeCarts($cart, $userCart);
        }
        $cart->user_id = Auth::id();
        $cart->save();
    }

    /**
     * Обеденить корзины.
     *
     * @param Cart $anonymous
     * @param Cart $userCart
     */
    protected function mergeCarts(Cart $anonymous, Cart $userCart)
    {
        foreach ($userCart->variations as $variation) {
            $pivot = $variation->pivot;
            $quantity = $pivot->quantity;
            $this->addToCart($variation, $quantity, $anonymous);
        }
        try {
            $userCart->delete();
        }
        catch (\Exception $exception) {
            Log::error("Не удалось удалить корзину {$userCart->id}");
            $userCart->user_id = null;
            $userCart->save();
        }
    }

    /**
     * Найти корзину по пользователю.
     *
     * @param $id
     * @return bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|Cart
     */
    protected function findCartByUserId($id)
    {
        $key = "cartByUserId:{$id}";
        return Cache::rememberForever($key, function () use ($id) {
            try {
                return Cart::query()
                    ->where("user_id", $id)
                    ->firstOrFail();
            }
            catch (\Exception $exception) {
                return false;
            }
        });
    }

    /**
     * Найти корзину по идентификатору.
     *
     * @param $uuid
     * @return bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|Cart
     */
    protected function findCartByUuid($uuid)
    {
        $key = "cartByUuid:{$uuid}";
        return Cache::rememberForever($key, function () use ($uuid) {
            try {
                return Cart::query()
                    ->where("uuid", $uuid)
                    ->firstOrFail();
            }
            catch (\Exception $exception) {
                return false;
            }
        });
    }
}