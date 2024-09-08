<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hashtag extends Model
{
    use HasFactory;

    protected $fillable = ["hashtag"];

    protected $hidden = ["updated_at", "created_at"];

    public function posthashTags()
    {
        return $this->hasMany(PostHashtag::class, "id", "hashtag_id");
    }
}
