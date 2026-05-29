<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
    ];

    // RELATION USER
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // RELATION CART ITEMS
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}