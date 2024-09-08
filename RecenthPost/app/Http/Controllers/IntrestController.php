<?php

namespace App\Http\Controllers;

use App\Service\PostService;
use Illuminate\Http\Request;

class IntrestController extends Controller
{
    public function markPostAsUnintrested(Request $request)
    {
        return PostService::markPostAsUnintrested($request);
    }
}
