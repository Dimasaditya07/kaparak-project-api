<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'total',
        'pickup_date',
        'return_date',
        'status',
        'payment_status',
        'note',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'return_date' => 'date',
    ];

    // RELATION USER
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // RELATION ITEMS
    public function reservationItems()
    {
        return $this->hasMany(ReservationItem::class);
    }

    // RELATION PAYMENT
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // RELATION RETURN
    public function return()
    {
        return $this->hasOne(ReturnModel::class);
    }
}