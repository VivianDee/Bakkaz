<?php

namespace App\Models;

use App\Impl\Services\AuthImpl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    use HasFactory;

    protected $fillable = ["post_id", "user_id"];

    protected $hidden = ["created_at", "updated_at"];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    protected $appends = ["user"];

    public function getUserAttribute()
    {
        $user = AuthImpl::getUserDetails($this->user_id);
        return $user;
    }
}
