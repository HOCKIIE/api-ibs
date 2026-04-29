<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \App\Casts\JsonUnicode;
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
        'descendant_th',
        'descendant_en',
        'descendant_ja',
        'status',
        'website',
        'apiName',
        'is_deleted',
    ];
    protected $dates = [
        'created_at',
        'published_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'descendant_th' => JsonUnicode::class,
        'descendant_en' => JsonUnicode::class,
        'descendant_ja' => JsonUnicode::class,
    ];

    public $timestamps = true;

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'brand_category', 'brand_id', 'category_id');
    }

    public function category()
    {
        return $this->belongsToMany(Category::class, 'brand_category', 'brand_id', 'category_id');
    }

    function getImageAttribute($value)
    {
        if ($value) {
            return asset($value);
        }
        return null;
    }

}
