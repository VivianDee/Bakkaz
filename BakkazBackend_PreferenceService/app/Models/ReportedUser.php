<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportedUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'privacy_id',
        'reported_user_id',
        'resolved',
        'reviewed',
    ];

    public function privacy()
    {
        return $this->belongsTo(Privacy::class);
    }
}
