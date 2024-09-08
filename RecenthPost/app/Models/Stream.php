<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Impl\Services\AuthImpl;

class Stream extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'channel_name',
        'is_live',
    ];

    protected $hidden = [
    "created_at",
    "updated_at"
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_live' => 'boolean',
    ];

    // Accessors
    protected $appends = [
        "user",
    ];

    /**
     * Get the user that owns the stream.
     */
     public function getUserAttribute():mixed
     {
         $user = AuthImpl::getUserDetails($this->user_id);
         return $user ?? [];
     }

}
