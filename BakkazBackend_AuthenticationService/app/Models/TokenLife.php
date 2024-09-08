<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenLife extends Model
{
    use HasFactory;

    protected $fillable = ["access_token_exp", "refresh_token_exp"];
}
