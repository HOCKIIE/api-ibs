<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'category';
    protected $primaryKey = 'id';
    protected $fillable = [
        'image',
        'name_th',
        'name_en',
        'name_jp',
        'description_th',
        'description_en',
        'description_jp',
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
        'status' => 'boolean',
        'is_deleted' => 'boolean',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    public function brand()
    {
        return $this->hasMany(Brand::class, 'category', 'id')
            ->where('is_deleted', 0)
            ->where('status', 1);
    }

    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_category', 'category_id', 'blog_id');
    }

}
