<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref_id',
        'ref_name',
        'action_type',
        'log_description',
        'meta_data'
    ];

    protected $hidden = [
        'meta_data'
    ];
}
