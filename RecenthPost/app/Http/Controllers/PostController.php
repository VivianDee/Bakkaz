<?php

namespace App\Http\Controllers;

use App\Service\PostService;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function createPost(Request $request)
    {
        return PostService::createPost($request);
    }

    public function deletePost(Request $request)
    {
        return PostService::deletePost($request);
    }

    public function deleteComment(Request $request)
    {
        return PostService::deleteComment($request);
    }
    public function deleteReplies(Request $request)
    {
        return PostService::deleteReplies($request);
    }

    public function sharePost(Request $request)
    {
        return PostService::sharePost($$request);
    }

    static function getAllPosts(Request $request)
    {
        return PostService::getAllPosts($request);
    }
}
