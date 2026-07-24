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

    public function summary(Request $request)
    {
        $user = $request->user();

        $cart = Cart::with('cartItems.product.category')
            ->where('user_id', $user->id)
            ->first();

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json([
                'cart_items' => [],
                'subtotal' => 0,
                'discount' => 0,
                'grand_total' => 0,
                'eligible_discount' => false,
            ]);
        }

        $purchaseCount = Reservation::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->count();

        $subtotal = $cart->cartItems->sum('subtotal');

        $eligibleDiscount = false;
        $discount = 0;

        if ($purchaseCount >= 3 && $subtotal >= 150000) {
            $eligibleDiscount = true;
            $discount = $subtotal * 0.10;
        }

        $grandTotal = $subtotal - $discount;

        return response()->json([
            'cart_items' => $cart->cartItems,
            'purchase_count' => $purchaseCount,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'grand_total' => $grandTotal,
            'eligible_discount' => $eligibleDiscount,
        ]);
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

            // ==========================
            // Hitung jumlah pembelian berhasil
            // ==========================
            $purchaseCount = Reservation::where('user_id', $user->id)
                ->where('payment_status', 'paid')
                ->count();

            // ==========================
            // Hitung subtotal
            // ==========================
            $subtotal = $cart->cartItems->sum('subtotal');

            $discount = 0;

            // Pembelian ke-4 dan seterusnya
            if ($purchaseCount >= 3 && $subtotal >= 150000) {
                $discount = $subtotal * 0.10;
            }

            $grandTotal = $subtotal - $discount;

            $firstItem = $cart->cartItems->first();

            // ==========================
            // Reservation
            // ==========================
            $reservation = Reservation::create([
                'user_id'        => $user->id,
                'code'           => 'RSV-' . time(),
                'total'          => $grandTotal,
                'pickup_date'    => $firstItem->start_date,
                'return_date'    => $firstItem->end_date,
                'status'         => 'pending',
                'payment_status' => 'unpaid',
            ]);

            foreach ($cart->cartItems as $item) {
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'product_id'     => $item->product_id,
                    'quantity'       => $item->quantity,
                    'price'          => $item->price,
                    'subtotal'       => $item->subtotal,
                ]);
            }

            // ==========================
            // Payment
            // ==========================
            $orderId = 'ORDER-' . time();

            $payment = Payment::create([
                'reservation_id' => $reservation->id,
                'order_id'       => $orderId,
                'payment_method' => 'midtrans',
                'amount'         => $grandTotal,
                'status'         => 'pending',
                'snap_token'     => null,
            ]);

            // ==========================
            // Midtrans
            // ==========================
            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $grandTotal,
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
                'message'          => 'Checkout berhasil',
                'snap_token'       => $snapToken,
                'reservation_id'   => $reservation->id,

                'purchase_count'   => $purchaseCount,
                'subtotal'         => $subtotal,
                'discount'         => $discount,
                'grand_total'      => $grandTotal,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
