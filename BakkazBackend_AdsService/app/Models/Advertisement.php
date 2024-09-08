<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',

        'price',

        // Status could be Expired or Active  
        'status',
        'clicks',
        'url',

        // Ad should automaticaly have a status of expired after seven days
        'start_date',
        'expiration_date',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "created_at",
        "updated_at",
    ];


    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_date' => 'datetime',
        'expiration_date' => 'datetime',
    ];


    public function click()
    {
        return $this->hasMany(Click::class);
    }

    public function asset()
    {
        return $this->hasMany(Asset::class);
    }


    public function review()
    {
        return $this->hasMany(Review::class);
    }
}
