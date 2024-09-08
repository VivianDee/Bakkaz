<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'first_name',
        'last_name',
        'email',
        'secret_key',
        'password',
        'country',
        'account_type',
        'ip_address',
        'media',
        'active_atatus',
        'state',
        "email_verified_at",
        'deleted',
        "remember_token",
        'deleted_at',
        'admin_tag',
        'gold_user'
    ];
    /*
    fields  'media',
    */

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret_key',
        'password',
        'password_history',

        'updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'secret_key' => 'hashed',
        'password' => 'hashed',

    ];

    public function logins()
    {
        return $this->hasMany(Login::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function passwords()
    {
        return $this->hasMany(Password::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function chatRooms()
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_user');
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }
}
