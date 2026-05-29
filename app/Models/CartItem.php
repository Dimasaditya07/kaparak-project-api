<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'start_date',
        'end_date',
        'duration',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // RELATION CART
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // RELATION PRODUCT
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}