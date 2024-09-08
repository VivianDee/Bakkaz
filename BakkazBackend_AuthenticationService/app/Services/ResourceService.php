<?php

namespace App\Services;

use App\Helpers\DateHelper;
use App\Helpers\ResponseHelpers;
use App\Interfaces\ResourceInterface;
use App\Models\Category;
use App\Models\Country;
use App\Models\SubCategory;
use App\Models\SubCategoryChild;
use Illuminate\Http\Request;

class ResourceService implements ResourceInterface
{
    public static function getCountries(Request $request)
    {
        $countries = Country::get();
        return ResponseHelpers::success(data: $countries);
    }
    public static function getCategories(Request $request)
    {
        $categories = Category::get();
        return ResponseHelpers::success(data: $categories);
    }
    public static function getSubCategories(Request $request)
    {
        $categories = SubCategory::with("category")
            ->with("sub_categories_children")
            ->get();
        return ResponseHelpers::success(data: [$categories]);
    }
    public static function getSubCategoriesChildren(Request $request)
    {
        $categories = SubCategoryChild::with("category")
            ->with("sub_category")
            ->get();
        return ResponseHelpers::success(data: [$categories]);
    }

    public static function getServerDate(Request $request)
    {
        return ResponseHelpers::success(data: ["server date"=>DateHelper::now()]);
    }
}
