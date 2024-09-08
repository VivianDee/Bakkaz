<?php

namespace App\Http\Controllers;

use App\Service\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        return SearchService::search($request);
    }
    public function sortPost(Request $request): \Illuminate\Http\JsonResponse
    {
        return SearchService::sortPost($request);
    }
}
