<?php

namespace App\Models;

use App\Helpers\UploadHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'code',
        'description',
        'stock',
        'price',
        'image',
        'status',
    ];

    // RELATION CATEGORY
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function reservationItems()
    {
        return $this->hasMany(ReservationItem::class);
    }

    protected $appends = [
        'image_url'
    ];

    public function getImageUrlAttribute()
    {
        if ($this->image) {

            $image = UploadHelper::getFileUrl($this->image);
            if (!$image) return null;
            return $image;
        }

        return null;
    }
}
