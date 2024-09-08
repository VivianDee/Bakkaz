<?php

namespace App\Interfaces;

use Illuminate\Http\Request;


interface ClickInterface
{
    /// Clicks
    static public function handleClick(Request $request);
}
