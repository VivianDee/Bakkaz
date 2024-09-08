<?php

namespace App\Http\Controllers;

use App\Services\ReportPostsService;
use Illuminate\Http\Request;

class ReportPostsController extends Controller
{
     /// Report Posts

     public function ReportPost(Request $request)
     {
         return ReportPostsService::ReportPost($request);
     }
 
     public function showReportedPosts(Request $request)
     {
         return ReportPostsService::showReportedPosts($request);
     }
}
