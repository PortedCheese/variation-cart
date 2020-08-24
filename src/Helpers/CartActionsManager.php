<?php

namespace PortedCheese\VariationCart\Helpers;

use App\Cart;
use App\ProductVariation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartActionsManager
{
    /**
     * Получить краткую информацию о корзине.
     *
     * @return object
     */
    public function getCartInfo()
    {
        $cart = $this->getCart();
        if ($cart) {
            $data = [
                "total" => $cart->total,
                "count" => $cart->variations()->count(),
            ];
        }
        else {
            $data = [
                "total" => 0,
                "count" => 0,
            ];
        }
        return (object) $data;
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
        if ($customCart) {
            $cart = $customCart;
        }
        else {
            $cart = $this->initCart();
        }
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
        try {
            return Cart::query()
                ->where("user_id", $id)
                ->firstOrFail();
        }
        catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Найти корзину по идентификатору.
     *
     * @param $uuid
     * @return bool|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|Cart
     */
    protected function findCartByUuid($uuid)
    {
        try {
            return Cart::query()
                ->where("uuid", $uuid)
                ->firstOrFail();
        }
        catch (\Exception $exception) {
            return false;
        }
    }
}