<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    public $fillable = [
        "user_id",
        'ip_address',
        'latitude',
        'longitude',
        'city',
        // REGION == STATE
        'region', 
        'country',
        'postal_code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_address',
    ];

    public function getFormattedAddressAttribute()
    {
        return "{$this->city}, {$this->region} {$this->postal_code}, {$this->country}";
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}