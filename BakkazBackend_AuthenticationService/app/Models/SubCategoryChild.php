<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategoryChild extends Model
{
    use HasFactory;
    protected $hidden = ["category_id", "sub_category_id"];
    protected $table = "sub_categories_child";

    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
