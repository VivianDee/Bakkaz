<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumPost extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'preference_id',
        'user_id',
        'status',
        'payment_ref',
        'payment_status',
        'payment_initialized_at',
        'payment_verified_at',
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
