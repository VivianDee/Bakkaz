<?php

namespace App\Helpers;

use App\Models\GroupedAsset;
use Illuminate\Support\Str;

class AssetHelpers
{
    public static function addPostalPublicIdToUrl($baseUrl, $postalPublicId)
    {
        // Define the possible positions
        $positions = [
            // 'w_200,g_north_west,x_10,y_10',  // Top-left
            // 'w_200,g_north,x_10,y_10',       // Top-center
            // 'w_200,g_north_east,x_10,y_10',  // Top-right
            'w_200,g_west,x_10,y_10',        // Middle-left
            // 'w_200,g_center,x_10,y_0',        // Center
            'w_200,g_east,x_10,y_10',        // Middle-right

            // 'w_150,g_north_west,x_20,y_20',  // Top-left, smaller
            // 'w_150,g_north_east,x_20,y_20',  // Top-right, smaller
            // 'w_150,g_center,x_20,y_20',      // Center, smaller

            // 'w_250,g_north_west,x_30,y_30',  // Top-left, larger
            // 'w_250,g_north_east,x_30,y_30',  // Top-right, larger
            // 'w_250,g_center,x_30,y_30',      // Center, larger

            // 'w_100,g_north,x_5,y_5',         // Top-center, smaller
            // 'w_100,g_center,x_5,y_5',        // Center, smaller

            // 'w_300,g_north_west,x_40,y_40',  // Top-left, extra large
            // 'w_300,g_north_east,x_40,y_40',  // Top-right, extra large
            // 'w_300,g_center,x_40,y_40',      // Center, extra large

            // 'w_200,g_center,x_20,y_20',       // Center with offset
            // 'w_200,g_center,x_-20,y_20',      // Center with negative offset
            // 'w_200,g_center,x_20,y_-20',      // Center with offset
            // 'w_200,g_center,x_-20,y_-20'      // Center with negative offset

        ];
        // Randomly select a position
        $randomPosition = $positions[array_rand($positions)];

        // Define the segment of the URL where the postalPublicId should be inserted
        $segmentToInsertAfter = '/upload/';

        // Find the position of the segmentToInsertAfter
        $insertPosition = strpos($baseUrl, $segmentToInsertAfter) + strlen($segmentToInsertAfter);

        // Construct the new URL by inserting the postalPublicId and the selected transformation
        $newUrl = substr($baseUrl, 0, $insertPosition) . 'l_' . $postalPublicId . ',' . $randomPosition . ',o_90/' . substr($baseUrl, $insertPosition);

        return $newUrl;
    }

    public static function extractPubliicId($url)
    {
        $parts = explode("/", $url);
        $lastPart = end($parts);
        $assetId = substr($lastPart, 0, strpos($lastPart, "."));
        return $assetId;
    }

    public static function generateUniqueGroupId()
    {
        $group_id = "";
        $existing_group_ids = [];

        // Generate an array of potential group IDs
        do {
            $potential_group_ids = [];
            for ($i = 0; $i < 10; $i++) {
                $potential_group_ids[] = Str::random(9);
            }

            // Check which of these IDs already exist in the database
            $existing_group_ids = GroupedAsset::whereIn(
                "group_id",
                $potential_group_ids
            )
                ->pluck("group_id")
                ->toArray();

            // Find the first potential group ID that doesn't exist in the database
            foreach ($potential_group_ids as $id) {
                if (!in_array($id, $existing_group_ids)) {
                    $group_id = $id;
                    break;
                }
            }
        } while ($group_id === "");

        return $group_id;
    }
}
