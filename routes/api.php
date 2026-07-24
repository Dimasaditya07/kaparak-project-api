<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// CONTROLLER
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\MidtransCallbackController;
use App\Http\Controllers\Api\PackageController;

Route::post('/callback', [MidtransCallbackController::class, 'handle']);

// AUTH
Route::post('/login', [
    AuthController::class,
    'login'
]);

Route::post('/register', [
    AuthController::class,
    'register'
]);

// PRODUCTS
Route::apiResource(
    'products',
    ProductController::class
);

// CATEGORIES
Route::apiResource(
    'categories',
    CategoryController::class
);

Route::apiResource('packages', PackageController::class);



Route::middleware('auth:sanctum')->group(function () {

    // GET LOGIN USER
    Route::get('/user', function (
        Request $request
    ) {
        return response()->json(
            $request->user()
        );
    });

    // LOGOUT
    Route::post('/logout', [
        AuthController::class,
        'logout'
    ]);


    Route::get('/cart', [
        CartController::class,
        'index'
    ]);

    Route::post('/cart', [
        CartController::class,
        'store'
    ]);

    Route::delete('/cart/{id}', [
        CartController::class,
        'destroy'
    ]);

    Route::apiResource(
        'reservations',
        ReservationController::class
    );


    Route::apiResource(
        'payments',
        PaymentController::class
    );


    Route::apiResource(
        'returns',
        ReturnController::class
    );

    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::get('/checkout/summary', [CheckoutController::class, 'summary']);
});


Route::middleware([
    'auth:sanctum',
    'admin'
])->group(function () {

    // USERS MANAGEMENT
    Route::get('/users', [
        UserController::class,
        'index'
    ]);

    // PRODUCT MANAGEMENT
    Route::post('/products', [
        ProductController::class,
        'store'
    ]);

    Route::put('/products/{product}', [
        ProductController::class,
        'update'
    ]);

    Route::delete('/products/{product}', [
        ProductController::class,
        'destroy'
    ]);
});
