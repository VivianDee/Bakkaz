<?php

namespace App\Models;

use App\Impl\Services\AuthImpl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "poll_question",
        "state",
        "city",
        "device",
        "expiresAt",
        // Add other attributes if needed
    ];
    // Relationships
    public function comments()
    {
        return $this->hasMany(Comment::class, "post_id", "id")
            ->with("reactions")
            ->with("replies");
    }

    public function views()
    {
        return $this->hasMany(View::class, "post_id", "id");
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class, "post_id", "id");
    }
    public function options()
    {
        return $this->hasMany(PollOption::class, "poll_id", "id")->with(
            "votes"
        );
    }

    // Accessors
    protected $appends = ["user", "assets"];

    public function getUserAttribute()
    {
        $user = AuthImpl::getUserDetails($this->user_id);
        return $user;
    }

    public function getAssetsAttribute()
    {
        // Assuming implementation is handled elsewhere
        return "";
    }
}
