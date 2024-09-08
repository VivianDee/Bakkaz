<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPlatform extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'platform_id',
        'access'
    ];

    protected $hidden = [
        'admin_id'
    ];
}
