<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Cek apakah email dan password cocok
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Email atau Password salah'], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('kaparak-token')->plainTextToken;

        // Kembalikan token dan role ke Next.js
        return response()->json([
            'message' => 'Login Berhasil',
            'token' => $token,
            'name' => $user->name,
            'role' => $user->role
        ], 200);
    }

    public function register(Request $request)
{
    // 1. Validasi Input
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed', // 'confirmed' akan otomatis mengecek input 'password_confirmation'
    ]);

   
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'customer',
    ]);

    // 3. Buat Token agar user langsung login setelah daftar
    $token = $user->createToken('kaparak-token')->plainTextToken;

    // 4. Kembalikan Response
    return response()->json([
        'message' => 'Registrasi Berhasil',
        'token' => $token,
        'name' => $user->name,
        'role' => $user->role
    ], 201);
}
}