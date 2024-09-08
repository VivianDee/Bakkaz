<?php

namespace App\Interfaces;

use Illuminate\Http\Request;


interface AdvertisementInterface
{

    /// Advertisemnets
    static public function showAd(Request $request);
    static public function createAd(Request $request);
    static public function updateAd(Request $request);
    static public function restoreAd(Request $request);
    static public function deleteAd(Request $request);

}
