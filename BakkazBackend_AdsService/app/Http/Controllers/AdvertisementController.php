<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AdvertisementService;

class AdvertisementController extends Controller
{

    /// Advertisement

    public function showAd(Request $request)
    {
        return AdvertisementService::showAd($request);
    }

    public function createAd(Request $request)
    {
        return AdvertisementService::createAd($request);
    }

    public function updateAd(Request $request)
    {
        return AdvertisementService::updateAd($request);
    }

    public function restoreAd(Request $request)
    {
        return AdvertisementService::restoreAd($request);
    }

    public function deleteAd(Request $request)
    {
        return AdvertisementService::deleteAd($request);
    }
}
