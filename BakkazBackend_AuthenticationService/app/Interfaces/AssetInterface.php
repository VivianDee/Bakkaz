<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface AssetInterface
{
    /**
     * Saves a new cover Asset for a user.
     *
     * @param Request $request The user ID.
     * @param mixed $data The cover Asset data (e.g., uploaded file, URL).
     * @return mixed Return value depends on implementation (e.g., success message, ID of saved asset).
     * @throws \Exception If saving fails.
     */
    public static function saveCoverAsset(Request $request);

    /**
     * Deletes the cover Asset for a user.
     *
     * @param Request $request The user ID.
     * @return mixed Return value depends on implementation (e.g., success message).
     * @throws \Exception If deletion fails.
     */
    public static function deleteLatestCoverAsset(Request $request);

    /**
     * Retrieves the cover Asset for a user.
     *
     * @param Request $request The user ID.
     * @return mixed Return value depends on implementation (e.g., asset data, URL).
     * @throws \Exception If retrieval fails.
     */
    public static function getCoverAsset(Request $request);

    /**
     * Retrieves the cover Asset History for a user.
     *
     * @param Request $request The user ID.
     * @return mixed Return value depends on implementation (e.g., asset data, URL).
     * @throws \Exception If retrieval fails.
     */
    public static function getCoverAssetHistory(Request $request);

    /**
     * Deletes the Cover Asset for a user by the asset id.
     *
     * @param Request $request The user ID.
     * @return mixed Return value depends on implementation (e.g., asset data, URL).
     * @throws \Exception If retrieval fails.
     */
    public static function deleteCoverAssetByAssetId(Request $request);

    /**
     * Saves a new profile Asset for a user.
     *
     * @param Request $request The user ID.
     * @param mixed $data The profile Asset data (e.g., uploaded file, URL).
     * @return mixed Return value depends on implementation (e.g., success message, ID of saved asset).
     * @throws \Exception If saving fails.
     */
    public static function saveProfileAsset(Request $request);

    /**
     * Deletes the profile Asset for a user.
     *
     * @param Request $request The user ID.
     * @return mixed Return value depends on implementation (e.g., success message).
     * @throws \Exception If deletion fails.
     */
    public static function deleteLatestProfileAsset(Request $request);

    /**
     * Retrieves the profile Asset for a user.
     *
     * @param Request $request The user ID.
     * @return mixed Return value depends on implementation (e.g., asset data, URL).
     * @throws \Exception If retrieval fails.
     */
    public static function getProfileAsset(Request $request);

    /**
     * Retrieves the profile Asset History for a user.
     *
     * @param Request $request The user ID.
     * @return mixed Return value depends on implementation (e.g., asset data, URL).
     * @throws \Exception If retrieval fails.
     */
    public static function getProfileAssetHistory(Request $request);

    /**
     * Deletes the profile Asset for a user by the asset id.
     *
     * @param Request $request The user ID.
     * @return mixed Return value depends on implementation (e.g., asset data, URL).
     * @throws \Exception If retrieval fails.
     */
    public static function deleteProfileAssetByAssetId(Request $request);
}
