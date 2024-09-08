<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedUser extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        //references the Privacy class
        'privacy_id',
        'blocked_user_id',
        'status'
    ];

    public function privacy() {
        return $this->belongsTo(Privacy::class);
    }
}
