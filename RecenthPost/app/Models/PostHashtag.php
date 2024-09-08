<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostHashtag extends Model
{
    use HasFactory;

    protected $fillable = ["post_id", "hashtag_id"];

    protected $hidden = ["updated_at", "created_at"];

    public function hashTag()
    {
        return $this->hasOne(Hashtag::class, "id", "hashtag_id");
    }
    public function post()
    {
        return $this->hasOne(Post::class, "id", "post_id");
    }
}
