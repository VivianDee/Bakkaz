<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = ["user_id", "ref_id", "ref_name", "post_notification"];

    protected $hidden = ["created_at", "updated_at"];

    public function post()
    {
        return $this->belongsTo(Post::class, "ref_id", "id");
    }
}
