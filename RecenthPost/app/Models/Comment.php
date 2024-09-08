<?php

namespace App\Models;

use App\Impl\Services\AuthImpl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["post_id", "user_id", "content"];

    protected $hidden = ["updated_at", "deleted_at"];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class)->with("reactions");
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    public function views()
    {
        return $this->hasMany(View::class, "comment_id");
    }

    // Accessors
    protected $appends = [
        "user",
        "replies_count",
        "views_count",
        "reactions_count",
    ];

    public function getUserAttribute()
    {
        $user = AuthImpl::getUserDetails($this->user_id);
        return $user ?? [];
    }

    public function getReactionsCountAttribute()
    {
        return count($this->reactions()->get());
    }
    public function getRepliesCountAttribute()
    {
        return count($this->replies()->get());
    }
    public function getViewsCountAttribute()
    {
        return count($this->views()->get());
    }

    public function isLikedBy($user_id)
    {
        if (!$user_id) {
            return [
                "reacted" => false,
                "type" => 0,
            ];
        }

        // Retrieve the reaction record
        $react = $this->reactions()->where("user_id", $user_id)->first();

        // Check if the reaction exists and if the type is within the valid range
        $type = $react ? (int) $react->type : 0;

        return [
            "reacted" => !is_null($react),
            "type" => $type,
        ];
    }
}
