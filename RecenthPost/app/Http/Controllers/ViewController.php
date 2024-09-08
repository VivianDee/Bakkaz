<?php

namespace App\Http\Controllers;

use App\Service\PostService;

use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function markAsViewed(Request $request)
    {
        return PostService::markAsView($request);
    }

    public function getViews(Request $request)
    {
        return PostService::getViews($request);
    }
}
