<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'permissions_id'
    ];

    protected $hidden = [
        'permissions_id'
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permissions_id');
    }
    
}
