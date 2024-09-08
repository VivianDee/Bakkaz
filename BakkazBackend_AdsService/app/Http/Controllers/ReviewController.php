<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Services\ReviewService;

class ReviewController extends Controller
{
    /// Review

    public function  reviewAd(Request $request)
    {
        return ReviewService::reviewAd($request);
    }

    public function updateReview(Request $request)
    {
        return ReviewService::updateReview($request);
    }

    public function deleteReview(Request $request)
    {
        return ReviewService::deleteReview($request);
    }
}
