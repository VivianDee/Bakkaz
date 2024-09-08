<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface GroupedAssetInteface
{
    // Grouped Asset
    public static function createGroupedAssets(Request $request);

    public static function deleteGroupedAssetsByGroupedAssetId(
        Request $request
    );
    public static function getGroupedAssetsByGroupedAssetId(Request $request);

    public static function updateGroupedAssetsByGroupedAssetId(
        Request $request
    );
    public static function updateSingleAssetOnGroupedAssetBySingleAssetId(
        Request $request
    );
    public static function getSingleAssetOnGroupedAssetBySingleAssetId(
        Request $request
    );
    public static function deleteSingleAssetOnGroupedAssetBySingleAssetId(
        Request $request
    );
}
