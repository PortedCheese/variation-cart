<?php

namespace PortedCheese\VariationCart\Helpers;

use App\Cart;
use App\Order;
use App\ProductVariation;
use Illuminate\Support\Facades\Cache;
use PortedCheese\ProductVariation\Events\CreateNewOrder;
use PortedCheese\ProductVariation\Facades\OrderActions;
use PortedCheese\ProductVariation\Http\Resources\ProductVariation as VariationResource;
use Illuminate\Support\Arr;
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
                    "count" => $cart->count,
                    "saleLess" => $cart->sale_less,
                    "discount" => $cart->discount,
                ];
            });
        }
        else {
            $data = [
                "total" => 0,
                "count" => 0,
                "saleLess" => 0,
                "discount" => 0,
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
            ->with("product", "product.cover")
            ->orderBy("price")
            ->get();
        foreach ($collection as $variation) {
            $product = $variation->product;
            $pivot = $variation->pivot;
            $items[] = (object) [
                "cover" => $product->cover,
                "product" => $product,
                "title" => $product->title,
                "variation" => $variation,
                "variationData" => new VariationResource($variation),
                "quantity" => $pivot->quantity,
            ];
        }
        return $items;
    }

    /**
     * Добавить в корзину.
     *
     * @param ProductVariation $variation
     * @param int $quantity
     * @param Cart|null $customCart
     * @return Cart
     */
    public function addToCart(ProductVariation $variation, $quantity = 1, Cart $customCart = null)
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
        $oldQuantity = DB::table("cart_product_variation")
            ->select("quantity")
            ->where("cart_id", $cart->id)
            ->where("product_variation_id", $variation->id)
            ->first();
        if ($oldQuantity) {
            $quantity += $oldQuantity->quantity;
        }
        $cart->variations()->syncWithoutDetaching([
            $variation->id => ["quantity" => $quantity]
        ]);
        $this->recalculateTotal($cart);
        return $cart;
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
        $cart->variations()->syncWithoutDetaching([
            $variation->id => ["quantity" => $quantity]
        ]);
        $this->recalculateTotal($cart);
        return $cart;
    }

    /**
     * Удалить из корзины.
     *
     * @param ProductVariation $variation
     */
    public function deleteItem(ProductVariation $variation)
    {
        $cart = $this->getCart();
        if (! $cart) {
            return;
        }
        $cart->variations()->detach($variation);
        $this->recalculateTotal($cart);
    }

    /**
     * Очистить корзину.
     *
     * @param Cart $cart
     */
    public function clearCart(Cart $cart)
    {
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
        $variations = [];
        foreach ($items as $item) {
            $variations[$item->variation->id] = $item->variation->pivot->quantity;
        }
        OrderActions::addVariationsToOrder($order, $variations);
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
        foreach ($cart->variations as $variation) {
            $pivot = $variation->pivot;
            $total += $variation->price * $pivot->quantity;
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
        $this->checkUserAuthCart($cart);
        return $cart;
    }

    /**
     * Если пользователь авторизован.
     *
     * @param Cart $cart
     */
    protected function checkUserAuthCart(Cart $cart)
    {
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