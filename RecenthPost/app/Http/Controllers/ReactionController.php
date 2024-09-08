<?php

namespace App\Http\Controllers;

use App\Service\PostService;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function getReactions(Request $request)
    {
        return PostService::getReactions($request);
    }

    public function reactOrUnreactPost(Request $request)
    {
        return PostService::reactOrUnreactPost($request);
    }
}
