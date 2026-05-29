<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Mass Assignment
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'image_ktp',
        'role',
    ];

    /**
     * Hidden Attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',

            // AUTO HASH PASSWORD
            'password' => 'hashed',
        ];
    }


    // USER -> CARTS
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // USER -> RESERVATIONS
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}