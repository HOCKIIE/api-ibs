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
        'category',
        'price',
        'status',
        'is_deleted',
    ];
    protected $casts = [
        'image' => 'string',
        'name_th' => 'string',
        'name_en' => 'string',
        'name_jp' => 'string',
        'description_th' => 'string',
        'description_en' => 'string',
        'description_jp' => 'string',
        'detail_th' => 'string',
        'detail_en' => 'string',
        'detail_jp' => 'string',
        'category' => 'int',
        'price' => 'float',
        'status' => 'boolean',
        'is_deleted' => 'boolean',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $dateFormat = 'Y-m-d H:i:s';
    public $incrementing = true;
    public $timestamps = true;

    public function category()
    {
        return $this->belongsTo(Category::class, "id", "category");
    }

    public function getImageAttribute($value)
    {
        return url('storage/' . $value);
    }
}
