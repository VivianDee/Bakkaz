<?php

namespace App\Models;

use App\Helpers\DateHelper;
use App\Impl\Services\AuthImpl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    public function __construct()
    {
        $this->expiration_time = DateHelper::addDays();
    }

    protected $fillable = [
        'achieved',
        "user_id",
        "category_id",
        "title",
        "content",
        "countries_id",
        "device",
        "expiration_time",
        "link",
        "all_countries",
        "email",
        "phone_code",
        "phone_number",
        "action",
        "plan",
        "package",
        "period",
        "state",
        "city",
        "paid_status",
        "file",
        "deleted_at",
    ];
    protected $hidden = [
        "deleted_at",
        "deleted",
        "updated_at",
        "expiration_time",
        "cv_path",
        "logo",
        "link",
        "file",
        "all_countries",
        "email",
        "phone_code",
        "phone_number",
        "action",
        "plan",
        "package",
        "period",
        "state",
        "city",
        "paid_status",
    ];

    // Relationships
    public function comments()
    {
        return $this->hasMany(Comment::class)
            ->with("reactions")
            ->with("replies");
    }

    public function views()
    {
        return $this->hasMany(View::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class, "post_id", "id");
    }

    public function shares()
    {
        return $this->hasMany(Share::class, "post_id", "id");
    }

    public function countries()
    {
        return $this->hasMany(Country::class, "post_id", "id");
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, "ref_id", "id");
    }

    public function hashtags()
    {
        return $this->hasMany(PostHashtag::class)->with("hashTag");
    }

    public function postHashtags()
    {
        return $this->hasMany(PostHashtag::class);
    }

    // Accessors
    protected $appends = [
        "user",
        "asset",
        "category",
        "reactions_count",
        "comments_count",
        "views_count",
    ];

    public function getUserAttribute()
    {
        $user = AuthImpl::getUserDetails($this->user_id);
        return $user ?? [];
    }

    public function getAssetAttribute()
    {
        if ($this->file == null) {
            return [];
        }
        $assets = AuthImpl::getGroupedAsset($this->file);
        return $assets;
    }

    public function getCategoryAttribute()
    {
        if ($this->category_id == null) {
            return null;
        }
        $categories = AuthImpl::getAllCategories();

        $foundCategory = null;

        foreach ($categories as $category) {
            if ($category["id"] === (int) $this->category_id) {
                $foundCategory = $category;
                break;
            }
        }

        if ($foundCategory) {
            return $foundCategory["name"];
        } else {
            return "Others";
        }
    }

    public function getReactionsCountAttribute()
    {
        return count($this->reactions()->get());
    }
    public function getCommentsCountAttribute()
    {
        return count($this->comments()->get());
    }
    public function getViewsCountAttribute()
    {
        return count($this->views()->get());
    }

    public function isLikedBy($user_id)
    {
        if (!$user_id) {
            return [
                "reacted" => false,
                "type" => 0,
            ];
        }

        // Retrieve the reaction record
        $react = $this->reactions()->where("user_id", $user_id)->first();

        // Check if the reaction exists and if the type is within the valid range
        $type = $react ? (int) $react->type : 0;

        return [
            "reacted" => !is_null($react),
            "type" => $type,
        ];
    }
}
