<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReturnModel extends Model
{
    use HasFactory;

    protected $table = 'returns';

    protected $fillable = [
        'reservation_id',
        'returned_at',
        'late_fee',
        'damage_fee',
        'note',
        'status',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    // RELATION RESERVATION
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}