<?php

namespace App\Services;

use App\Enums\AssetType;
use App\Helpers\ResponseHelpers;
use App\Impl\CloudinaryImpl;
use App\Interfaces\AssetInterface;
use App\Models\Asset;
use App\Models\User;
use App\Helpers\AssetHelpers;
use App\Models\GroupedAsset;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AssetsServices implements AssetInterface
{
  
    /// profile asset
    public static function saveProfileAsset(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                "media" => "required|image|mimes:jpeg,jpg,png,heic",
            ]);
            throw_if($validator->fails(), new ValidationException($validator));

            $asset = Asset::where("asset_type", AssetType::ProfileAsset->value)
                ->where("user_id", $request->user()->id)
                ->update([
                    "deleted" => true,
                    "deleted_at" => now(),
                ]);

            $nonOptimizedImage = CloudinaryImpl::uploadImage(
                $request->media->getRealPath(),
                AssetType::ProfileAsset->value
            );

            Asset::create([
                "user_id" => $user->id,
                "asset_type" => AssetType::ProfileAsset->value,
                "path" => $nonOptimizedImage["secure_url"],
                "mime_type" => $request->media->getMimeType(),
            ]);

            return ResponseHelpers::success(message: "Asset Saved");
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
    public static function deleteLatestProfileAsset(Request $request)
    {
        try {
            $user = $request->user();

            $asset = Asset::where("asset_type", AssetType::ProfileAsset->value)
                ->where("user_id", $user->id)
                ->update([
                    "deleted" => true,
                    "deleted_at" => now(),
                ]);

            if ($asset) {
                return ResponseHelpers::success(message: "Asset deleted");
            }

            return ResponseHelpers::notFound();
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
    public static function getProfileAsset(Request $request)
    {
        try {

            if ($request->route('id')) {
                $user = User::where('id', $request->route('id'))->first();
            } else {
                $user = $request->user();
            }

            $asset = Asset::where("asset_type", AssetType::ProfileAsset->value)
                ->where("user_id", $user->id)
                ->where("deleted", false)
                ->get()
                ->first();

            if ($asset) {
                return ResponseHelpers::success([$asset]);
            }

            return ResponseHelpers::notFound();
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
    public static function getProfileAssetHistory(Request $request)
    {
        try {
            $user = $request->user();

            $asset = Asset::where("asset_type", AssetType::ProfileAsset->value)
                ->where("user_id", $user->id)
                ->get();

            if ($asset) {
                return ResponseHelpers::success($asset);
            }

            return ResponseHelpers::notFound();
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
    public static function deleteProfileAssetByAssetId(Request $request)
    {
        try {
            $asset_id = $request->query("id");
            $user_id = $request->user()->id;
            $hard_delete = $request->query("hard_delete");

            if ($hard_delete == true) {
                /// get image publicId for cloud delete
                $asset = Asset::where(
                    "asset_type",
                    AssetType::ProfileAsset->value
                )
                    ->where("user_id", $user_id)
                    ->where("id", $asset_id)
                    ->get()
                    ->first();

                if ($asset) {
                    $extractedPublicId = AssetHelpers::extractPubliicId(
                        $asset->path
                    );

                    $cloudDeleted = CloudinaryImpl::hardDeleteImage(
                        $asset->asset_type . "/" . $extractedPublicId
                    );

                    if ($cloudDeleted) {
                        $asset->delete();

                        return ResponseHelpers::success(
                            message: "Asset deleted"
                        );
                    }

                    return ResponseHelpers::error(
                        message: "Error Deleting Asset"
                    );
                }
            } else {
                $asset = Asset::where(
                    "asset_type",
                    AssetType::ProfileAsset->value
                )
                    ->where("user_id", $user_id)
                    ->where("id", $asset_id)
                    ->update([
                        "deleted" => true,
                        "deleted_at" => now(),
                    ]);

                if ($asset) {
                    return ResponseHelpers::success(message: "Asset deleted");
                }
            }

            return ResponseHelpers::notFound();
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    /// cover asset
    public static function saveCoverAsset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "media" => "required|image|mimes:jpeg,jpg,png,heic",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            $asset = Asset::where("asset_type", AssetType::CoverAsset->value)
                ->where("user_id", $request->user()->id)
                ->update([
                    "deleted" => true,
                    "deleted_at" => now(),
                ]);

            $nonOptimizedImage = CloudinaryImpl::uploadImage(
                $request->media->getRealPath(),
                AssetType::CoverAsset->value
            );

            Asset::create([
                "user_id" => $request->user()->id,
                "asset_type" => AssetType::CoverAsset->value,
                "path" => $nonOptimizedImage["secure_url"],
                "mime_type" => $request->media->getMimeType(),
            ]);

            return ResponseHelpers::success(message: "Asset Saved");
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
    public static function deleteLatestCoverAsset(Request $request)
    {
        try {
            $user = $request->user();

            $asset = Asset::where("asset_type", AssetType::CoverAsset->value)
                ->where("user_id", $user->id)
                ->update([
                    "deleted" => true,
                    "deleted_at" => now(),
                ]);

            if ($asset) {
                return ResponseHelpers::success(message: "Cover Asset deleted");
            }

            return ResponseHelpers::notFound();
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
    public static function getCoverAsset(Request $request)
    {
        try {
            $user = $request->user();

            $asset = Asset::where("asset_type", AssetType::CoverAsset->value)
                ->where("user_id", $user->id)
                ->where("deleted", false)
                ->get()
                ->first();

            if ($asset) {
                return ResponseHelpers::success([$asset]);
            }

            return ResponseHelpers::notFound();
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
    public static function getCoverAssetHistory(Request $request)
    {
        try {
            $user = $request->user();

            $asset = Asset::where("asset_type", AssetType::CoverAsset->value)
                ->where("user_id", $user->id)
                ->get();

            if ($asset) {
                return ResponseHelpers::success($asset);
            }

            return ResponseHelpers::notFound();
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
    public static function deleteCoverAssetByAssetId(Request $request)
    {
        try {
            $asset_id = $request->query("id");
            $user_id = $request->user()->id;
            $hard_delete = $request->query("hard_delete");

            if ($hard_delete == true) {
                /// get image publicId for cloud delete
                $asset = Asset::where(
                    "asset_type",
                    AssetType::CoverAsset->value
                )
                    ->where("user_id", $user_id)
                    ->where("id", $asset_id)
                    ->get()
                    ->first();

                if ($asset) {
                    $extractedPublicId = AssetHelpers::extractPubliicId(
                        $asset->path
                    );

                    $cloudDeleted = CloudinaryImpl::hardDeleteImage(
                        $asset->asset_type . "/" . $extractedPublicId
                    );

                    if ($cloudDeleted) {
                        $asset->delete();

                        return ResponseHelpers::success(
                            message: "Asset deleted"
                        );
                    }

                    return ResponseHelpers::error(
                        message: "Error Deleting Asset"
                    );
                }
            } else {
                $asset = Asset::where(
                    "asset_type",
                    AssetType::CoverAsset->value
                )
                    ->where("user_id", $user_id)
                    ->where("id", $asset_id)
                    ->update([
                        "deleted" => true,
                        "deleted_at" => now(),
                    ]);

                if ($asset) {
                    return ResponseHelpers::success(message: "Asset deleted");
                }
            }

            return ResponseHelpers::notFound();
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "media",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }


    /// Guests
    static public function getUserProfileAsset(Request $request)
    {
        try {
            $user_id = $request->route('user_id');

            $asset = Asset::where("asset_type", AssetType::ProfileAsset->value)
                ->where("user_id", $user_id)
                ->get();

            if ($asset) {
                return ResponseHelpers::success($asset);
            }

            return ResponseHelpers::notFound();
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    static public function getUserCoverAsset(Request $request)
    {
        try {
            $user_id = $request->route('user_id');

            $asset = Asset::where("asset_type", AssetType::CoverAsset->value)
                ->where("user_id", $user_id)
                ->get();

            if ($asset) {
                return ResponseHelpers::success($asset);
            }

            return ResponseHelpers::notFound();
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
}
