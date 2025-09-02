<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    protected $table = 'contact';
    protected $fillable = [
        'firstName',
        'lastName',
        'phone',
        'message',
        'email',
        'source',
        'created_at',
        'updated_at'
    ];
}
