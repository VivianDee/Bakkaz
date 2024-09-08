<?php

namespace App\Impl;

use App\Interfaces\DigitalOceanCdnInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigitalOceanCdnImpl implements DigitalOceanCdnInterface
{

    static public function purge($fileName)
    {
        $folder = config('filesystems.do.folder');
        Http::asJson()->delete(
            config('filesystems.do.cdn_endpoint') . '/cache',
            [
                'files' => ["{$folder}/{$fileName}"],
            ]
        );
    }

    static public function uploadImage($realPath, $sub_folder = null)
    {
        $fileName = (string) Str::uuid();
        $folder = config('filesystems.disks.do.folder');

        $sub_folder = null;

        $upload_path = $sub_folder ? "{$folder}/{$sub_folder}/{$fileName}" : "{$folder}/{$fileName}";

        $status = Storage::disk('do')->put(
            $upload_path,
            file_get_contents($realPath)
        );

        return $status;
    }

    static public function hardDeleteImage($fileName, $sub_folder = null)
    {
        $folder = config('filesystems.disks.do.folder');

        $upload_path = $sub_folder ? "{$folder}/{$sub_folder}/{$fileName}" : "{$folder}/{$fileName}";

        $status = Storage::disk('do')->delete($upload_path);

        Self::purge($fileName);

        return $status;
    }

    static public function hardUpdateImage($realPath, $sub_folder = null, $fileName)
    {
        $folder = config('filesystems.disks.do.folder');

        $upload_path = $sub_folder ? "{$folder}/{$sub_folder}/{$fileName}" : "{$folder}/{$fileName}";

        $status = Storage::disk('do')->put(
            $upload_path,
            file_get_contents($realPath)
        );

        Self::purge($fileName);

        return $status;
    }

    public static function determineFileType($filePath): string
    {
        if (!file_exists($filePath)) {
            return "auto"; // File does not exist or unknown type
        }

        $mimeType = mime_content_type($filePath);

        $imageTypes = [
            "image/jpeg",
            "image/png",
            "image/bmp",
            "image/webp",
            "image/tiff",
            "image/heic",
        ];

        $videoTypes = [
            "video/mp4",
            "video/mpeg",
            "video/quicktime",
            "video/x-ms-wmv",
            "video/x-msvideo",
            "video/x-flv",
            "video/webm",
            "video/3gpp",
            "video/3gpp2",
        ];

        $documentTypes = [
            "application/pdf",
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
            "application/vnd.ms-excel",
            "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "application/vnd.ms-powerpoint",
            "application/vnd.openxmlformats-officedocument.presentationml.presentation",
            "text/plain",
            "text/csv",
            "application/rtf",
        ];

        $audioTypes = [
            "audio/mpeg",
            "audio/wav",
            "audio/ogg",
            "audio/mp4",
            "audio/aac",
            "audio/webm",
            "audio/x-ms-wma",
            "audio/x-wav",
            "audio/m4a",
        ];

        if (in_array($mimeType, $imageTypes)) {
            return "image";
        } elseif (in_array($mimeType, $videoTypes)) {
            return "video";
        } elseif (in_array($mimeType, $documentTypes)) {
            return "raw";
        } elseif (in_array($mimeType, $audioTypes)) {
            return "auto";
        } elseif ($mimeType == "image/gif") {
            return "raw";
        } else {
            return "auto";
        }
    }
}
