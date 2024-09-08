<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupedAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["ref_id", "group_id", "asset_type"];

    protected $hidden = ["deleted_at", "created_at", "updated_at"];

    public function assets()
    {
        return $this->hasMany(Asset::class, "group_id");
    }
}
