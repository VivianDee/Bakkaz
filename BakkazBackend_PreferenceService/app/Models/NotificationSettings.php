<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSettings extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'preference_id',
        'new_favourite',
        'likes',
        'direct_messages',
        'post_comments',
        'post_replies',
        'general_notifications'
    ];

    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }

}
