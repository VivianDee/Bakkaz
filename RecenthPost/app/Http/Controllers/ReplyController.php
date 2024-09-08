<?php

namespace App\Http\Controllers;

use App\Service\PostService;
use Illuminate\Http\Request;

class ReplyController extends Controller
{
    public function replyToPost(Request $request)
    {
        return PostService::replyToPost($request);
    }

    public function getReplies(Request $request)
    {
        return PostService::getReplies($request);
    }
}
