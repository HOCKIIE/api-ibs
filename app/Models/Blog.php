<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \App\Models\Category;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blog';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'draftId',
        'image',
        'title_th',
        'title_en',
        'title_ja',
        'description_th',
        'description_en',
        'description_ja',
        'detail_th',
        'detail_en',
        'detail_ja',
        'published_at',
        'status',
        'pathName'
    ];
    protected $dates = [
        'created_at',
        'published_at',
        'updated_at',
        'deleted_at',
    ];

    public $timestamps = true;

    function getImageAttribute($value)
    {
        if ($value) {
            return asset($value);
        }
        return null;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_category', 'blog_id', 'category_id');
    }

    public function category()
    {
        return $this->belongsToMany(Category::class, 'blog_category', 'blog_id', 'category_id');
    }
    
}
