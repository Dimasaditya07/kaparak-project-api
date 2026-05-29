<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = Cart::with('cartItems.product')
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'data' => $cart
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $product = Product::findOrFail(
            $request->product_id
        );

        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id
        ]);

        $duration = now()
            ->parse($request->start_date)
            ->diffInDays($request->end_date);

        $subtotal =
            $product->price *
            $request->quantity *
            $duration;

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration' => $duration,
            'price' => $product->price,
            'subtotal' => $subtotal
        ]);

        return response()->json([
            'message' => 'Berhasil tambah cart',
            'data' => $item
        ], 201);
    }

    public function destroy(string $id)
    {
        $item = CartItem::findOrFail($id);

        $item->delete();

        return response()->json([
            'message' => 'Cart item berhasil dihapus'
        ]);
    }
}