<?php

namespace App\Http\Controllers;

use App\Services\ResourceService;

use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function getCountries(Request $request)
    {
        return ResourceService::getCountries($request);
    }
    public function getCategories(Request $request)
    {
        return ResourceService::getCategories($request);
    }
    public function getSubCategories(Request $request)
    {
        return ResourceService::getSubCategories($request);
    }
    public function getSubCategoriesChildren(Request $request)
    {
        return ResourceService::getSubCategoriesChildren($request);
    }
    public function getServerDate(Request $request)
    {
        return ResourceService::getServerDate($request);
    }
}
