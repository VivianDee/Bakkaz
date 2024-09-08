<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    use HasFactory;

    public $fillable = [

        "user_id",
        "logged_in_at",
        "created_at",
        "updated_at",
    ];


    protected $casts = [
        'logged_in_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
