<?php

namespace App\Interfaces;

use Illuminate\Http\Request;


interface ReviewInterface
{
    /// Review
    static public function reviewAd(Request $request);
    static public function showReviews(Request $request);
    static public function updateReview(Request $request);
    static public function deleteReview(Request $request);
}
