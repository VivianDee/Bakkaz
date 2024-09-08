<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserStat extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'posts_count',
        'replies_count',
        'comments_count',
        'likes_count',
        'shares_count',
        'views_count',
        '1_star_count',
        '2_star_count',
        '3_star_count',
        '4_star_count',
        '5_star_count',
    ];

}
