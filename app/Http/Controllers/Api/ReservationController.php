<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Reservation;
use App\Models\ReservationItem;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with([
            'user',
            'reservationItems.product'
        ])->latest()->get();

        return response()->json([
            'data' => $reservations
        ]);
    }

    public function store(Request $request)
    {
        $cart = Cart::with('cartItems.product')
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cart || $cart->cartItems->count() === 0) {
            return response()->json([
                'message' => 'Cart kosong'
            ], 400);
        }

        $total = $cart->cartItems
            ->sum('subtotal');

        $reservation = Reservation::create([
            'user_id' => $request->user()->id,
            'code' => 'RSV-' . time(),
            'total' => $total,
            'pickup_date' => $cart->cartItems->first()->start_date,
            'return_date' => $cart->cartItems->first()->end_date,
            'status' => 'unpaid',
            'payment_status' => 'unpaid',
        ]);

        foreach ($cart->cartItems as $item) {

            ReservationItem::create([
                'reservation_id' => $reservation->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal
            ]);

            $item->product->decrement(
                'stock',
                $item->quantity
            );
        }

        $cart->cartItems()->delete();

        return response()->json([
            'message' => 'Checkout berhasil',
            'data' => $reservation
        ]);
    }
}
