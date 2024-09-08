<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "ref_id",
        "ref_url",
        "ref_type"
    ];

    protected $hidden = ["updated_at", "created_at"];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
