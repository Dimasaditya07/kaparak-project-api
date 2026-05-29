<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReservationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
    ];

    // RELATION RESERVATION
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    // RELATION PRODUCT
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}