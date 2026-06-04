<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Helpers\UploadHelper;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::latest()->get();

        return response()->json([
            'data' => $payments
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric',
            'proof' => 'nullable|image'
        ]);

        $proof = null;

        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $path = 'payments';
            $fileName = UploadHelper::uploadFile($file, $path);
            $proof = $path . '/' . $fileName;
        }

        $payment = Payment::create([
            'reservation_id' => $request->reservation_id,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'proof' => $proof,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Payment berhasil dibuat',
            'data' => $payment
        ], 201);
    }
}
