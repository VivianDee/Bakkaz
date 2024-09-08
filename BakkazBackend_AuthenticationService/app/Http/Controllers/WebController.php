<?php

namespace App\Http\Controllers;

use App\Services\WebService;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function contactUs(Request $request) {
        return WebService::contactUs($request);
    }

    public function subscribe(Request $request) {
        return WebService::subscribe($request);
    }
}
