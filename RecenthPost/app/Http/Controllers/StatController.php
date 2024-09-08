<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelpers;
use App\Service\StatService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Test\Constraint\ResponseHeaderSame;

class StatController extends Controller
{
  

    public function getUserStat(Request $request)
    {
        return  ResponseHelpers::success( StatService::getUserStats($request->user_id), "Stat fetched successfully");

    }

    public function createUserStats(Request $request) {
        return  ResponseHelpers::success( StatService::createUserStats($request->input('user_id')), "Stats created successfully");
    }
}
