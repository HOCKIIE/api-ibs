<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \App\Casts\JsonUnicode;
use \App\Models\Category;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blog';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'draftId',
        'image_th',
        'image_en',
        'image_ja',
        'title_th',
        'title_en',
        'title_ja',
        'description_th',
        'description_en',
        'description_ja',
        'detail_th',
        'detail_en',
        'detail_ja',
        'descendant_th',
        'descendant_en',
        'descendant_ja',
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
    protected $casts = [
        'descendant_th' => JsonUnicode::class,
        'descendant_en' => JsonUnicode::class,
        'descendant_ja' => JsonUnicode::class,
    ];

    public $timestamps = true;

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_category', 'blog_id', 'category_id');
    }

    public function category()
    {
        return $this->belongsToMany(Category::class, 'blog_category', 'blog_id', 'category_id');
    }

    function getImageAttribute($value)
    {
        return ($value) ? asset($value) : null;
    }

    public function getImageThAttribute($value)
    {
        return $value ? asset($value) : null;
    }

    public function getImageEnAttribute($value)
    {
        return $value ? asset($value) : null;
    }

    public function getImageJaAttribute($value)
    {
        return $value ? asset($value) : null;
    }
}
