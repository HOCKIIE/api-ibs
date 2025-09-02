<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;
use \App\Models\Category;

class Brand extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'brand';
    protected $primaryKey = 'id';
    protected $fillable = [
        'image',
        'title_th',
        'title_en',
        'title_jp',
        'description_th',
        'description_en',
        'description_jp',
        'detail_th',
        'detail_en',
        'detail_jp',
        'status',
        'website',
        'apiName',
        'is_deleted',
    ];
    protected $casts = [
        'image' => 'string',
        'title_th' => 'string',
        'title_en' => 'string',
        'title_jp' => 'string',
        'description_th' => 'string',
        'description_en' => 'string',
        'description_jp' => 'string',
        'status' => 'boolean',
        'is_deleted' => 'boolean',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $timestamps = true;

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'brand_category', 'brand_id', 'category_id');
    }

    public function product(){
        return $this->hasMany(Product::class,'product_brand','brand_id','product_id');
    }

    function getImageAttribute($value)
    {
        if ($value) {
            return asset($value);
        }
        return null;
    }

}
