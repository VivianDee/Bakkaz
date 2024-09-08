<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportedPost extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'privacy_id',
        'reported_post_id',
        'reason',
        'description',
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
    ];

    public function privacy()
    {
        return $this->belongsTo(Privacy::class);
    }
}
