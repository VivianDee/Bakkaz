<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ClickService;

class ClickController extends Controller
{

    /// Clicks

    public function handleClick(Request $request)
    {
        return ClickService::handleClick($request);
    }
}
