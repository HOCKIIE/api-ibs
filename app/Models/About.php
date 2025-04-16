<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    protected $table = 'about_us';
    protected $fillable = [
        'title_th',
        'title_en',
        'title_jp',
        'description_th',
        'description_en',
        'description_jp',
    ];
    protected $casts = [
        'title_th' => 'string',
        'title_en' => 'string',
        'title_jp' => 'string',
        'description_th' => 'string',
        'description_en' => 'string',
        'description_jp' => 'string',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
    ];
    protected $dateFormat = 'Y-m-d H:i:s';
}
