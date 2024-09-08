<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'permissions_id',
        'admin_id',
        'preference_status'
    ];

    protected $hidden = [
        'preference_status'
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permissions_id');
    }
}
