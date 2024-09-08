<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    public $fillable = [
        "user_id",
        "asset_type",
        "path",
        "size",
        "group_id",
        "mime_type",
        "created_at",
        "updated_at",
        "deleted_at",
        "deleted",
    ];

    protected $hidden = ["deleted_at", "created_at", "updated_at", "deleted"];

    public function groupAsset()
    {
        return $this->belongsTo(GroupedAsset::class);
    }
}
