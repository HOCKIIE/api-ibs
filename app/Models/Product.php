<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';
    protected $fillable = [
        'image',
        'name_th',
        'name_en',
        'name_jp',
        'description_th',
        'description_en',
        'description_jp',
        'detail_th',
        'detail_en',
        'detail_jp',
        'price',
        'status',
        'published_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $dateFormat = 'Y-m-d H:i:s';
    public $incrementing = true;
    public $timestamps = true;

    public function brand()
    {
        return $this->belongsToMany(Brand::class, 'product_brand', 'product_id', 'brand_id');
    }
}
