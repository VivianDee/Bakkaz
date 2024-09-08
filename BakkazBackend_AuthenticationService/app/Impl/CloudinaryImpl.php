<?php

namespace App\Impl;

use Cloudinary\Cloudinary;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Exception\Error;
use Illuminate\Support\Facades\Log;

class CloudinaryImpl
{
    protected static $cloudinary;

    public static function getInstance(): Cloudinary
    {
        if (!self::$cloudinary) {
            $config = [
                "cloud_name" => env("CLOUDINARY_CLOUD_NAME"),
                "api_key" => env("CLOUDINARY_API_KEY"),
                "api_secret" => env("CLOUDINARY_API_SECRET"),
            ];
            self::$cloudinary = new Cloudinary($config);
        }

        return self::$cloudinary;
    }

    public static function uploadImage(
        string $imagePath,
        string $folder,
        int $width = null,
        int $height = null,
        string $quality = "auto",
        string $fetch = "auto",
        string $crop = "scale"
    ): ApiResponse {
        $cloudinary = self::getInstance();
        $response = $cloudinary->uploadApi()->upload($imagePath, [
            "resource_type" => self::determineFileType($imagePath),
            "folder" => $folder,
            "transformation" => [
                "width" => $width,
                "height" => $height,
                "quality" => $quality,
                "fetch_format" => $fetch,
                "crop" => $crop,
            ],
            "eager" => [
                [
                    "width" => $width,
                    "height" => $height,
                    'crop' => $crop,
                ]
            ],
            "eager_async" => true,
        ]);
        return $response;
    }

    /*
       EXCERCISE  CAUTION WITH THIS FUNCTION
       */
       public static function hardDeleteImage(string $publicId, ?array $options = []): bool
       {
           try {
               // Get the Cloudinary instance
               $cloudinary = self::getInstance();

               // Call the destroy method to delete the image
             $data =   $cloudinary->uploadApi()->destroy($publicId, ["indalidate"=>true]);

               return true; // Assuming success if no exception is thrown
           } catch (Error $e) {
               // Handle Cloudinary API errors
               Log::error('Cloudinary image deletion failed: ' . $e->getMessage());
               return false;
           } catch (\Exception $e) {
               // Handle general exceptions
               Log::error('Error deleting image: ' . $e->getMessage());
               return false;
           }
       }


    public static function hardUpdateImage(
        string $publicId,
        string $path,
        string $folder,
        ?array $options = []
    ): bool {
        $cloudinary = self::getInstance();
        $deleted = self::hardDeleteImage($publicId, $options = []);

        if ($deleted) {
            self::uploadImage($path, $folder);
        }

        $response = $cloudinary->uploadApi()->destroy($publicId, $options = []);
        return $response["result"];
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
