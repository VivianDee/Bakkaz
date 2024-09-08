<?php

namespace App\Models;

use App\Impl\Services\AuthImpl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "post_id",
        "comment_id",
        "reply_id",
        "type",
    ];

    protected $hidden = ["updated_at", "created_at"];

    public function post()
    {
        return $this->belongsTo(Post::class, "post_id", "id");
    }

    public function comment()
    {
        return $this->belongsTo(Comment::class, "comment_id", "id");
    }

    public function reply()
    {
        return $this->belongsTo(Reply::class, "reply_id", "id");
    } // Accessors
    protected $appends = ["user"];

    public function getUserAttribute()
    {
        $user = AuthImpl::getUserDetails($this->user_id);
        return $user;
    }
}
