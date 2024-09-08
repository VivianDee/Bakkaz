<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $hidden = ["created_at", "updated_at"];

    public function sub_categories()
    {
        return $this->hasMany(SubCategory::class)->with(
            "sub_categories_children"
        );
    }
}
