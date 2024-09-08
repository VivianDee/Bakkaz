<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "plan_id",
        "status",
        "payment_ref",
        "payment_initialized_at",
        "payment_verified_at",
    ];
    protected $hidden = [
        "payment_initialized_at",
        "payment_verified_at",
        "created_at",
        "updated_at",
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
