<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationItem;
use Illuminate\Support\Facades\DB;
use Midtrans\Snap;
use Midtrans\Config;


class CheckoutController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function checkout(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with('cartItems.product')
            ->where('user_id', $user->id)
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json([
                'message' => 'Cart kosong'
            ], 400);
        }

        DB::beginTransaction();

        try {

            $firstItem = $cart->cartItems->first();

            $reservation = Reservation::create([
                'user_id' => $user->id,
                'code' => 'RSV-' . time(),
                'total' => $cart->cartItems->sum('subtotal'),
                'pickup_date' => $firstItem->start_date,
                'return_date' => $firstItem->end_date,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            foreach ($cart->cartItems as $item) {
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $orderId = 'ORDER-' . time();

            $payment = Payment::create([
                'reservation_id' => $reservation->id,
                'order_id' => $orderId,
                'payment_method' => 'midtrans',
                'amount' => $reservation->total,
                'status' => 'pending',
                'snap_token' => null,
            ]);

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $reservation->total,
                ],
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            $payment->update([
                'snap_token' => $snapToken
            ]);

            DB::commit();

            return response()->json([
                'snap_token' => $snapToken,
                'reservation_id' => $reservation->id,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
