<?php

namespace App\Models;

use App\Impl\Services\AuthImpl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["comment_id", "user_id", "content"];
    protected $hidden = ["updated_at", "deleted_at"];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    // Accessors
    protected $appends = ["user", "reactions_count"];

    public function getUserAttribute()
    {
        $user = AuthImpl::getUserDetails($this->user_id);

        return $user ?? [];
    }

    public function getReactionsCountAttribute()
    {
        return count($this->reactions()->get());
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
