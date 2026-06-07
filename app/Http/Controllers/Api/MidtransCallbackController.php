<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // Validasi signature Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        $notification = new Notification();
        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $paymentType = $notification->payment_type;

        $payment = Payment::where('order_id', $orderId)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        try {
            if (in_array($transactionStatus, ['settlement', 'capture'])) {
                DB::transaction(function () use ($payment, $paymentType) {

                    // 1. Update status payment
                    $payment->update([
                        'status'         => 'paid',
                        'payment_method' => $paymentType,
                    ]);

                    // 2. Update reservasi
                    Reservation::where('id', $payment->reservation_id)->update([
                        'payment_status' => 'paid',
                        'status'         => 'confirmed',
                    ]);

                    // 3. Kosongkan keranjang berdasarkan user_id dari reservasi
                    $userId = $payment->user_id
                        ?? optional($payment->reservation)->user_id; // fallback ke relasi

                    if ($userId) {
                        $cartIds = Cart::where('user_id', $userId)->pluck('id');

                        if ($cartIds->isNotEmpty()) {
                            CartItem::whereIn('cart_id', $cartIds)->delete();
                            Cart::whereIn('id', $cartIds)->delete();
                        }
                    }
                });
            } elseif ($transactionStatus === 'pending') {
                $payment->update(['status' => 'pending']);
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                DB::transaction(function () use ($payment) {
                    $payment->update(['status' => 'failed']);

                    Reservation::where('id', $payment->reservation_id)->update([
                        'payment_status' => 'failed',
                    ]);
                });
            }
        } catch (\Exception $e) {
            Log::error('Midtrans callback error: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Internal Server Error'], 500);
        }

        return response()->json(['message' => 'OK']);
    }
}
