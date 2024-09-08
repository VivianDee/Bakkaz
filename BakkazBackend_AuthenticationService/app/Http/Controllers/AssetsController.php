<?php

namespace App\Http\Controllers;

use App\Services\AssetsServices;
use App\Services\GroupedAssetServices;

use Illuminate\Http\Request;

class AssetsController extends Controller
{


    // Grouped Asset
    public static function getDownloadableUrl(Request $request)
    {
        return GroupedAssetServices::getDownloadableUrl($request);
    }

    /// Profile

    public function saveProfileAsset(Request $request)
    {
        return AssetsServices::saveProfileAsset($request);
    }

    public function getProfileAssetHistory(Request $request)
    {
        return AssetsServices::getProfileAssetHistory($request);
    }

    public function deleteLatestProfileAsset(Request $request)
    {
        return AssetsServices::deleteLatestProfileAsset($request);
    }

    public function getProfileAsset(Request $request)
    {
        return AssetsServices::getProfileAsset($request);
    }
    public function deleteProfileAssetByAssetId(Request $request)
    {
        return AssetsServices::deleteProfileAssetByAssetId($request);
    }

    /// Cover
    public function saveCoverAsset(Request $request)
    {
        return AssetsServices::saveCoverAsset($request);
    }

    public function getCoverAssetHistory(Request $request)
    {
        return AssetsServices::getCoverAssetHistory($request);
    }

    public function deleteLatestCoverAsset(Request $request)
    {
        return AssetsServices::deleteLatestCoverAsset($request);
    }

    public function getCoverAsset(Request $request)
    {
        return AssetsServices::getCoverAsset($request);
    }
    public function deleteCoverAssetByAssetId(Request $request)
    {
        return AssetsServices::deleteCoverAssetByAssetId($request);
    }

    // Grouped Asset
    public static function createGroupedAssets(Request $request)
    {
        return GroupedAssetServices::createGroupedAssets($request);
    }

    public static function deleteGroupedAssetsByGroupedAssetId(Request $request)
    {
        return GroupedAssetServices::deleteGroupedAssetsByGroupedAssetId(
            $request
        );
    }
    public static function getGroupedAssetsByGroupedAssetId(Request $request)
    {
        return GroupedAssetServices::getGroupedAssetsByGroupedAssetId($request);
    }

    public static function updateGroupedAssetsByGroupedAssetId(Request $request)
    {
        return GroupedAssetServices::updateGroupedAssetsByGroupedAssetId(
            $request
        );
    }

    // single
    public static function updateSingleAssetOnGroupedAssetBySingleAssetId(
        Request $request
    ) {
        return GroupedAssetServices::updateSingleAssetOnGroupedAssetBySingleAssetId(
            $request
        );
    }
    public static function getSingleAssetOnGroupedAssetBySingleAssetId(
        Request $request
    ) {
        return GroupedAssetServices::getSingleAssetOnGroupedAssetBySingleAssetId(
            $request
        );
    }
    public static function deleteSingleAssetOnGroupedAssetBySingleAssetId(
        Request $request
    ) {
        return GroupedAssetServices::deleteSingleAssetOnGroupedAssetBySingleAssetId(
            $request
        );
    }


    /// Guests
    public function getUserProfileAsset(Request $request)
    {
        return AssetsServices::getUserProfileAsset($request);
    }

    public function getUserCoverAsset(Request $request)
    {
        return AssetsServices::getUserCoverAsset($request);
    }
}
