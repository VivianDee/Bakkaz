<?php

namespace App\Http\Controllers;

use App\Service\PostService;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function sharePost(Request $request)
    {
       return PostService::sharePost($request);
    }

    public function getSharesByPost(Request $request)
    {
        return PostService::getSharesByPost($request);
    }

    public function getSharesByUser(Request $request)
    {
        return PostService::getSharesByUser ($request);
    }
}
