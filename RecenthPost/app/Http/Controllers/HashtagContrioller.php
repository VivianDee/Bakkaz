<?php

namespace App\Http\Controllers;

use App\Service\HashTagService;
use App\Service\PostService;
use Illuminate\Http\Request;

class HashtagContrioller extends Controller
{
    public function get(Request $request)
    {
        return HashTagService::getAllHashTag($request);
    }
}
