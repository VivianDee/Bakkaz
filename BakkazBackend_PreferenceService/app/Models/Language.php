<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        "language",
        "language_code",
    ];
    protected $hidden = [
        "created_at",
        "updated_at",
    ];
}
