<?php

namespace PortedCheese\VariationCart\Http\Controllers\Site;

use App\CartProductVariationSet;
use App\CartProductVariationSetAddon;
use App\Http\Controllers\Controller;
use App\Order;
use App\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PortedCheese\VariationCart\Facades\CartActions;

class CartController extends Controller
{
    /**
     * Просмотр корзины.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $cartItems = CartActions::getCartItems();
        $cart = CartActions::getCartInfo();
        return view("variation-cart::site.cart.index", compact("cart", "cartItems"));
    }

    /**
     * Оформление заказа.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function checkout(Request $request)
    {
        $cartItems = CartActions::getCartItems();
        $cart = CartActions::getCartInfo();
        if (! $cartItems || $cart->count <= 0) {
            return redirect()
                ->route("catalog.cart.index");
        }
        $user = Auth::check() ? Auth::user() : false;
        return view("variation-cart::site.cart.checkout", compact("cartItems", "cart", "request", "user"));
    }

    /**
     * Создать заказ по корзине.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submit(Request $request)
    {
        $cart = CartActions::getCart();
        if (! $cart) {
            return redirect()
                ->route("catalog.cart.index")
                ->with("danger", "Корзина не найдена");
        }
        $this->submitValidator($request->all());
        $order = CartActions::makeOrder($cart, $request->all());
        return redirect()
            ->route("catalog.cart.complete", ["order" => $order, "check" => $order->uuid]);
    }

    /**
     * @param array $data
     */
    protected function submitValidator(array $data)
    {
        Validator::make($data, [
            "name" => ["required", "max: 250"],
            "email" => ["nullable", "required_without:phone", "email"],
            "phone" => ["required_without:email"],
            "privacy_policy" => ["accepted"],
        ], [
            "email.required_without" => "Поле :attribute обязательно когда :values не заполнено.",
            "phone.required_without" => "Поле :attribute обязательно когда :values не заполнено.",
            "privacy_policy.accepted" => "Требуется согласие с политикой конфиденциальности",
        ], [
            "name" => "Имя",
            "email" => "E-mail",
            "phone" => "Телефон",
        ])->validate();
    }

    /**
     * Заказ оформлен.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function completePage(Request $request, Order $order, $check)
    {
        if ($check != $order->uuid) {
            abort(404);
        }
        $items = $order
            ->items()
            ->whereNull("order_item_set_id")
            ->select("order_items.*")
            ->join("products", "order_items.product_id", "products.id")
            ->with("product", "product.cover")
            ->orderBy("products.title")
            ->get();
        $addons = [];
        foreach ($items as $item){
            if ($sets = $item->orderItemSets){
                foreach ($sets as $set){
                    $addons[$item->id][] = $set->addons;
                }
            }
        }
        return view("variation-cart::site.cart.complete", compact("order", "items", "addons"));
    }

    /**
     * Удалить вариацию из корзины.
     *
     * @param ProductVariation $variation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteItem(ProductVariation $variation)
    {
        CartActions::deleteItem($variation);
        return redirect()
            ->back()
            ->with("success", "Товар удален из корзины");
    }

    /**
     * Удалить дополнение из комплекта корзины.
     *
     * @param CartProductVariationSetAddon $addon
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAddonItem(CartProductVariationSetAddon $addon)
    {
        CartActions::deleteAddonItem($addon);
        return redirect()
            ->back()
            ->with("success", "Дополнение удалено из корзины");
    }

    /**
     * Удалить дополнение из комплекта корзины.
     *
     * @param CartProductVariationSet $set
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteSet(CartProductVariationSet $set)
    {
        CartActions::deleteSet($set);
        return redirect()
            ->back()
            ->with("success", "Комплект удален из корзины");
    }

    /**
     * Добавление вариации в корзину.
     *
     * @param Request $request
     * @param ProductVariation $variation
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request, ProductVariation $variation)
    {
        $this->addToCartValidator($request->all());
        $addons = $request->get("addons")? $request->get("addons"):[];
        $cart = CartActions::addToCart($variation, $request->get("quantity"), $addons);
        $result = session()->pull("addToCartResult", [
            "success" => true,
            "message" => ! count($addons) ? "Товар добавлен в корзину": "Товар и дополнения добавлены в корзину"
        ]);
        return response()
            ->json([
                "success" => $result["success"],
                "message" => $result["message"],
                "cart" => CartActions::getCartInfo($cart)
            ]);
    }

    /**
     * @param $data
     */
    protected function addToCartValidator($data)
    {
        Validator::make($data, [
            "quantity" => ["required", "numeric", "min:1"],
            "addons" => ["array"],
            "addons." => ["object"],
            "addons.id" => ["numeric", "min:1"],
            "addons.quantity" => ["numeric", "min:1"],
        ], [], [
            "quantity" => "Количество товара",
            "addons" => "Дополнения",
            "addons." => "Дополнение",
            "addons.id" => "Дополнение",
            "addons.quantity" => "Количество дополнения",
        ])->validate();
    }

    /**
     * Изменить количество.
     *
     * @param Request $request
     * @param ProductVariation $variation
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeQuantity(Request $request, ProductVariation $variation)
    {
        $this->changeQuantityValidator($request->all());
        $quantity = $request->get("quantity");
        $cart = CartActions::changeQuantity($variation, $quantity);
        $result = session()->pull("addToCartResult", [
            "success" => true,
            "message" => "Количество изменено"
        ]);
        $class = config("product-variation.productVariationResource");
        return response()
            ->json([
                "success" => $result["success"],
                "message" => $result["message"],
                "cart" => CartActions::getCartInfo($cart),
                "variation" => new $class($variation),
            ]);
    }

    /**
     * Изменить количество.
     *
     * @param Request $request
     * @param ProductVariation $variation
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeAddonQuantity(Request $request, ProductVariation $variation)
    {
        $this->changeQuantityValidator($request->all());
        $quantity = $request->get("quantity");
        $setId = $request->get("set");
        $addonId = $request->get("addon");
        $cart = CartActions::changeAddonQuantity($quantity, $setId, $addonId);
        $result = session()->pull("addToCartResult", [
            "success" => true,
            "message" => "Количество изменено"
        ]);
        $class = config("product-variation.productVariationResource");
        return response()
            ->json([
                "success" => $result["success"],
                "message" => $result["message"],
                "cart" => CartActions::getCartInfo($cart),
                "variation" => new $class($variation),
            ]);
    }

    /**
     * @param $data
     */
    protected function changeQuantityValidator($data)
    {
        Validator::make($data, [
            "quantity" => ["required", "numeric", "min:1"],
            "set" => ["nullable", "numeric", "min:1"],
            "addon" => ["nullable","numeric", "min:1"],
        ], [], [
            "quantity" => "Количество",
            "set" => "Комплект",
            "addon" => "Дополенние",
        ])->validate();
    }
}
