<?php

namespace App\Services;

use App\Helpers\ResponseHelpers;
use App\Impl\CloudinaryImpl;
use App\Models\Asset;
use App\Helpers\AssetHelpers;
use App\Impl\DigitalOceanCdnImpl;
use App\Models\GroupedAsset;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Interfaces\GroupedAssetInteface;
use Illuminate\Support\Facades\Log;

class GroupedAssetServices implements GroupedAssetInteface
{
    public static function getDownloadableUrl(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "url" => "required|string",
        ]);

        throw_if($validator->fails(), new ValidationException($validator));

        $downloadable_url = AssetHelpers::addPostalPublicIdToUrl($request->url, 'profile-asset:l_RP_WHITEB_LOGO_b9b6qu');

        return ResponseHelpers::success(["downloadable_url" => $downloadable_url]);
    }



    public static function createGroupedAssets(Request $request)
    {
        ini_set("upload_max_filesize", "2000M");
        ini_set("post_max_size", "2000M");

        // Validate input
        $validator = Validator::make($request->all(), [
            "media_files" => "nullable|array",
            "media_files.*" => "nullable|max:209715200",
            "asset_type" => "sometimes|string|required",
            "media_files_urls" => "sometimes|array",
        ]);

        if ($validator->fails()) {
            return ResponseHelpers::error($validator->errors()->first());
        }

        // Generate unique group ID
        $primary_id = $request->header("service") . "/" . $request->get("asset_type");
        $group_id = AssetHelpers::generateUniqueGroupId();

        try {
            // Check if media_files_urls are provided and handle them
            if ($request->get('media_files_urls')) {
                $media_files_urls = $request->get('media_files_urls');

                if (!empty($media_files_urls)) {
                    // Create a grouped asset
                    $groupedAsset = GroupedAsset::create([
                        "ref_id" => $request->header("service"),
                        "group_id" => $group_id,
                        "asset_type" => $primary_id,
                    ]);

                    // Create assets from URLs
                    foreach ($media_files_urls as $media_file_url) {
                        Asset::create([
                            "user_id" => null,
                            "group_id" => $groupedAsset->id,
                            "asset_type" => $primary_id,
                            "path" => $media_file_url,
                            "mime_type" => "url/upload",
                        ]);
                    }

                    return ResponseHelpers::success(["group_id" => $group_id]);
                }
            }

            // Check if media_files are provided and handle them
            if ($request->hasFile("media_files")) {
                $uploadStatuses = [];

                foreach ($request->file("media_files") as $mediaFile) {
                    // Assume CloudinaryImpl::uploadImage handles the upload
                    $status = DigitalOceanCdnImpl::uploadImage(
                        $mediaFile->getRealPath(),
                        $primary_id
                    );
                    $uploadStatuses[] = [
                        "status" => $status,
                        "mediaFile" => $mediaFile,
                    ];
                }

                if (empty($uploadStatuses)) {
                    return ResponseHelpers::unprocessableEntity("File upload failed. Please try again.");
                }

                // Create a grouped asset
                $groupedAsset = GroupedAsset::create([
                    "ref_id" => $request->header("service"),
                    "group_id" => $group_id,
                    "asset_type" => $primary_id,
                ]);

                // Save each uploaded file asset
                foreach ($uploadStatuses as $uploadStatus) {
                    Asset::create([
                        "user_id" => null,
                        "group_id" => $groupedAsset->id,
                        "asset_type" => $primary_id,
                        "path" => $uploadStatus["status"]["secure_url"],
                        "mime_type" => $uploadStatus["mediaFile"]->getMimeType(),
                    ]);
                }

                return ResponseHelpers::success(["group_id" => $group_id]);
            }

            // If neither files nor URLs are provided, return an error message
            return ResponseHelpers::unprocessableEntity("No files or URLs provided for upload.");

        } catch (ValidationException $e) {
            return ResponseHelpers::error($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error creating grouped assets: ' . $e->getMessage());
            return ResponseHelpers::error("An unexpected error occurred.");
        }
    }




    public static function deleteGroupedAssetsByGroupedAssetId(Request $request)
    {
        try {
            $groupedAsset = GroupedAsset::find($request->route("group_id"));

            if (!$groupedAsset) {
                return ResponseHelpers::notFound();
            }

            $assets = Asset::where(
                "group_id",
                $request->route("group_id")
            )->get();

            if ($assets) {
                foreach ($assets as $asset) {
                    $asset->delete();
                }
            }

            // Soft delete the grouped asset
            $groupedAsset->delete();

            return ResponseHelpers::success(message: "Grouped Asset deleted");
        } catch (Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    public static function getGroupedAssetsByGroupedAssetId(Request $request)
    {
        try {
            $groupedAsset = GroupedAsset::where(
                "group_id",
                request()->route("group_id")
            )
                ->with(["assets"])
                ->get()
                ->first();

            if ($groupedAsset) {
                return ResponseHelpers::success($groupedAsset);
            }

            return ResponseHelpers::notFound();
        } catch (Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    public static function updateGroupedAssetsByGroupedAssetId(Request $request)
    {
        try {
            return $request;

            $groupedAsset = GroupedAsset::where(
                "group_id",
                $request->route("group_id")
            )->first();

            if (!$groupedAsset) {
                return ResponseHelpers::notFound();
            }

            // Execute the update query with proper data types
            $oldAssets = Asset::where("group_id", $groupedAsset->id)->get();

            if ($oldAssets) {
                foreach ($oldAssets as $oldAsset) {
                    $oldAsset->delete();
                }
            }

            // Initialize an array to store upload statuses
            $uploadStatuses = [];

            return $request;

            // Upload files
            foreach ($request->media_files as $mediaFile) {
                $status = CloudinaryImpl::uploadImage(
                    $mediaFile->getRealPath(),
                    $groupedAsset->asset_type
                );
                // var_dump($status);
                // Store the status along with the media file details
                $uploadStatuses[] = [
                    "status" => $status,
                    "mediaFile" => $mediaFile,
                ];
            }

            if (count($uploadStatuses) <= 0) {
                return ResponseHelpers::unprocessableEntity();
            }

            // Create asset entries
            foreach ($uploadStatuses as $uploadStatus) {
                Asset::create([
                    "user_id" => null,
                    "group_id" => $groupedAsset->id,
                    "asset_type" => $groupedAsset->asset_type,
                    "path" => $uploadStatus["status"]["secure_url"],
                    "mime_type" => $uploadStatus["mediaFile"]->getMimeType(),
                ]);
            }

            return ResponseHelpers::success(message: "Grouped Asset updated");
        } catch (Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    public static function updateSingleAssetOnGroupedAssetBySingleAssetId(
        Request $request
    ) {
        try {
            $asset = Asset::where("id", $request->route("single_asset_id"))
                ->where("group_id", $request->route("group_id"))
                ->where("deleted", false)
                ->first();

            if (!$asset) {
                return ResponseHelpers::notFound();
            }
            // return $asset;

            // Handle file upload
            if ($request->hasFile("media")) {
                $status = CloudinaryImpl::uploadImage(
                    $request->file("media")->getRealPath(),
                    $asset->asset_type
                );

                // Update asset details
                $asset->update([
                    "path" => $status["secure_url"],
                    "mime_type" => $request->file("media")->getMimeType(),
                ]);
                return ResponseHelpers::success(
                    message: "Single Asset updated"
                );
            }
            return ResponseHelpers::unprocessableEntity(
                message: "File Not Picked"
            );
        } catch (Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    public static function getSingleAssetOnGroupedAssetBySingleAssetId(
        Request $request
    ) {
        try {
            $asset = Asset::where("id", $request->route("single_asset_id"))
                ->where("group_id", $request->route("group_id"))
                ->where("deleted", false)
                ->first();

            if ($asset) {
                return ResponseHelpers::success($asset);
            }

            return ResponseHelpers::notFound();
        } catch (Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    public static function deleteSingleAssetOnGroupedAssetBySingleAssetId(
        Request $request
    ) {
        try {
            $asset = Asset::where("id", $request->route("single_asset_id"))
                ->where("group_id", $request->route("group_id"))
                ->where("deleted", false)
                ->first();

            if (!$asset) {
                return ResponseHelpers::notFound();
            }

            // Soft delete the single asset
            $asset->update(["deleted" => true, "deleted_at" => now()]);

            return ResponseHelpers::success(message: "Single Asset deleted");
        } catch (Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }
}
