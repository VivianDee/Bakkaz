<?php

namespace App\Http\Controllers;

use App\service\PollService;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function createPost(Request $request)
    {
        return PollService::createPost($request);
    }

    public function voteOnPoll(Request $request)
    {
        return PollService::voteOnPoll($request);
    }

    public function pollVotes(Request $request)
    {
        return PollService::pollVotes($request);
    }
}
