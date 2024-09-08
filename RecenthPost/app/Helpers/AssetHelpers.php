<?php
class AssetHelpers
{
    public static function extractPubliicId($url)
    {
        $parts = explode("/", $url);
        $lastPart = end($parts);
        $assetId = substr($lastPart, 0, strpos($lastPart, "."));
        return $assetId;
    }

    public static function generateGroupId()
    {
        return;
    }
}
