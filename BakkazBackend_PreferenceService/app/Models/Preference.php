<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Preference extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'is_verified',
        'subscribed',
        'subscription_id',
        'language', 
        'service'   
    ];

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function privacy()
    {
        return $this->hasOne(Privacy::class);
    }

    public function security()
    {
        return $this->hasOne(Security::class);
    }

    public function custom_id()
    {
        return $this->hasOne(CustomId::class);
    }

    public function premium_post()
    {
        return $this->hasOne(PremiumPost::class);
    }

    public function verification()
    {
        return $this->hasOne(Verifications::class);
    }

    public function notification_settings()
    {
        return $this->hasOne(NotificationSettings::class);
    }
}
