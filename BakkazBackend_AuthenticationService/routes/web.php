<?php

use App\Http\Controllers\GoogleLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
