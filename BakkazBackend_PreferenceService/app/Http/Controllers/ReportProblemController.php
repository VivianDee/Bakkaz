<?php

namespace App\Http\Controllers;

use App\Services\ReportProblemService;
use Illuminate\Http\Request;

class ReportProblemController extends Controller
{
     /// Report Problem

     public function ReportProblem(Request $request)
     {
         return ReportProblemService::ReportProblem($request);
     }
 
     public function showReportedProblem(Request $request)
     {
         return ReportProblemService::showReportedProblem($request);
     }
}
