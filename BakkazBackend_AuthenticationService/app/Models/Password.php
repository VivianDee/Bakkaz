<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Password extends Model
{
    use HasFactory;

    public $fillable = [
        "id",
        'user_id',
        'password',
        'changed_at'
    ];
    protected $hidden = [
        'password',
        'created_at',
        'updated_at' ,
    ];

    protected $casts = [
        'changed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
