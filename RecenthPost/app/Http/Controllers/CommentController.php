<?php

namespace App\Http\Controllers;

use App\Service\PostService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function commentOnPost(Request $request)
    {
        return PostService::commentOnPost($request);
    }

    public function getComments(Request $request)
    {
        return PostService::getComments($request);
    }
}
