<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ["post_id", "country_iso"];

    protected $hidden = ["created_at", "updated_at", "id"];

    public function post()
    {
        return $this->belongsTo(Post::class, "post_id", "id");
    }
}
