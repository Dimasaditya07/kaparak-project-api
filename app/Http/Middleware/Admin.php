<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pastikan user sudah login dan role-nya 'admin'
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Kalau bukan admin (atau belum login), tolak dengan kode 403 Forbidden
        return response()->json([
            'message' => 'Akses ditolak. Anda bukan Admin Kaparak!'
        ], 403);
    }
}