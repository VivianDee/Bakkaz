<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Privacy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'preference_id',
        'visibility',
        'privacy_mode',
        'show_online_status',
        'is_mentionable'
    ];

    public function preference()
    {
        return $this->belongsTo(Preference::class);
    }

    public function blockedUsers()
    {
        return $this->hasMany(BlockedUser::class);
    }

    public function reportedUsers()
    {
        return $this->hasMany(ReportedUser::class);
    }

    public function reportedPosts()
    {
        return $this->hasMany(ReportedPost::class);
    }

    public function mutedUsers()
    {
        return $this->hasMany(MutedUser::class);
    }

    public function reportedProblems()
    {
        return $this->hasMany(ReportProblem::class);
    }
}
