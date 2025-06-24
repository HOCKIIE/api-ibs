<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;
use App\Models\egory;

class Brand extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'brand';
    protected $fillable = [
        'image',
        'name_th',
        'name_en',
        'name_jp',
        'description_th',
        'description_en',
        'description_jp',
        'category',
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
        'category' => 'int',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    // public function product()
    // {
    //     return $this->hasMany(Product::class, 'brand', 'id')
    //         ->where('is_deleted', 0)
    //         ->where('status', 1);
    // }
    public function product(){
        return $this->hasMany(Product::class,'product_brand','brand_id','product_id');
    }

}
