<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \App\Models\Category;

class Blog extends Model
{
    protected $table = 'blog';
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
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
        'status'
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public $timestamps = false;

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_category', 'blog_id', 'category_id');
    }
}
