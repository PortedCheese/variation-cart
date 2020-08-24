<?php

namespace PortedCheese\VariationCart\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PortedCheese\VariationCart\Facades\CartActions;

class CartController extends Controller
{
    public function addToCart(Request $request, ProductVariation $variation)
    {
        $this->addToCartValidator($request->all());
        $cart = CartActions::addToCart($variation, $request->get("quantity"));
        return response()
            ->json([
                "success" => true,
                "message" => "OK",
                "cart" => (object) [
                    "total" => $cart->total,
                ]
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
