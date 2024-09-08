<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollOption extends Model
{
    use HasFactory;
     protected $fillable = [
        'poll_id',
        'option_value',
        'poll_option_votes',
    ];

    public function votes()
    {
        return $this->hasMany(PollVote::class, "poll_option_id", "id");
    }
}

