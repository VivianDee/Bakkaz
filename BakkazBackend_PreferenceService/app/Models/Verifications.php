<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verifications extends Model
{
    use HasFactory;

    protected $fillable = [
        "preference_id",
        "gid_status",
        "file",
        "status",
        "payment_ref",
        "payment_status",
        "payment_initialized_at",
        "payment_verified_at",
    ];
    protected $hidden = [
        "payment_initialized_at",
        "payment_verified_at",
        "created_at",
        "updated_at",
    ];

    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }
}
