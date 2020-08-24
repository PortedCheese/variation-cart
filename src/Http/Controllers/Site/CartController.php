<?php

namespace PortedCheese\VariationCart\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\ProductVariation;
use Illuminate\Http\Request;
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
        $cart = CartActions::getCartItems();
        return view("variation-cart::site.cart.index", compact("cart"));
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
                "cart" => variation_cart()->getCartInfo($cart)
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
}
