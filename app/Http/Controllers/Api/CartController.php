<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Carbon\Carbon;

class CartController extends Controller
{
    /**
     * Menampilkan cart milik user
     */
    public function index(Request $request)
    {
        $cart = Cart::with('cartItems.product')
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'data' => $cart
        ]);
    }

    /**
     * Tambah produk ke cart
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        // Ambil produk
        $product = Product::findOrFail($request->product_id);

        // Cari atau buat cart user
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id
        ]);

        // Hitung lama sewa (jumlah malam)
        $startDate = Carbon::parse($request->start_date);
        $endDate   = Carbon::parse($request->end_date);

        $duration = $startDate->diffInDays($endDate);

        if ($duration < 1) {
            return response()->json([
                'message' => 'Minimal penyewaan adalah 1 malam.'
            ], 422);
        }

        // Hitung subtotal
        $subtotal = $product->price * $request->quantity * $duration;

        // Simpan cart item
        $item = CartItem::create([
            'cart_id'     => $cart->id,
            'product_id'  => $product->id,
            'quantity'    => $request->quantity,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'duration'    => $duration,
            'price'       => $product->price,
            'subtotal'    => $subtotal,
        ]);

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
            'data' => $item
        ], 201);
    }

    /**
     * Hapus item dari cart
     */
    public function destroy(Request $request, string $id)
    {
        $item = CartItem::whereHas('cart', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($id);

        $item->delete();

        return response()->json([
            'message' => 'Cart item berhasil dihapus.'
        ]);
    }
}
