<?php

namespace App\Models;

use App\Helpers\UploadHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'image',
        'package_price',
        'normal_price',
        'discount_amount',
        'status',
    ];

    protected $appends = [
        'image_url'
    ];

    public function packageItems()
    {
        return $this->hasMany(PackageItem::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {

            $image = UploadHelper::getFileUrl($this->image);

            if (!$image) {
                return null;
            }

            return $image;
        }

        return null;
    }
}
