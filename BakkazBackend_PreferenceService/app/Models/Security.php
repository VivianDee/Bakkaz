<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Security extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'preference_id',
        'remember_me',
        'biometric_id',
        'face_id',
        'sms_authenticator',
        'google_authenticator'
    ];

    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }
}
