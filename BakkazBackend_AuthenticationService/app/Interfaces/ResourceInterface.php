<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface ResourceInterface
{
    public static function getCountries(Request $request);
    public static function getCategories(Request $request);
    public static function getSubCategories(Request $request);
    public static function getSubCategoriesChildren(Request $request);
}
