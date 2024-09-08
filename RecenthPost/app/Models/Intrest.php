<?php

namespace App\Models;

use App\Impl\Services\AuthImpl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intrest extends Model
{
    use HasFactory;

    protected $table = "interests";

    protected $fillable = ["user_id", "ref_id", "ref_name"];

    protected $hidden = ["created_at", "updated_at"];

    // Accessors
    protected $appends = ["user"];

    public function getUserAttribute()
    {
        $user = AuthImpl::getUserDetails($this->user_id);
        return $user;
    }
}
