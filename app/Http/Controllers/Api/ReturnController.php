<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnModel;

class ReturnController extends Controller
{
    public function index()
    {
        $returns = ReturnModel::latest()->get();

        return response()->json([
            'data' => $returns
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required',
            'late_fee' => 'nullable|numeric',
            'damage_fee' => 'nullable|numeric',
            'note' => 'nullable'
        ]);

        $return = ReturnModel::create([
            'reservation_id' => $request->reservation_id,
            'returned_at' => now(),
            'late_fee' => $request->late_fee ?? 0,
            'damage_fee' => $request->damage_fee ?? 0,
            'note' => $request->note,
            'status' => 'returned'
        ]);

        return response()->json([
            'message' => 'Pengembalian berhasil',
            'data' => $return
        ]);
    }
}