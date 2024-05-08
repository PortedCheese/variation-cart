<?php

namespace PortedCheese\VariationCart\Http\Controllers\Site;

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
            ->select("order_items.*")
            ->join("products", "order_items.product_id", "products.id")
            ->with("product", "product.cover")
            ->orderBy("products.title")
            ->get();
        return view("variation-cart::site.cart.complete", compact("order", "items"));
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
     * Добавление вариации в корзину.
     *
     * @param Request $request
     * @param ProductVariation $variation
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request, ProductVariation $variation)
    {
        $this->addToCartValidator($request->all());
        $cart = CartActions::addToCart($variation, $request->get("quantity"));
        $result = session()->pull("addToCartResult", [
            "success" => true,
            "message" => "Товар добавлен в корзину"
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
        ], [], [
            "quantity" => "Количество",
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
     * @param $data
     */
    protected function changeQuantityValidator($data)
    {
        Validator::make($data, [
            "quantity" => ["required", "numeric", "min:1"],
        ], [], [
            "quantity" => "Количество",
        ])->validate();
    }
}
