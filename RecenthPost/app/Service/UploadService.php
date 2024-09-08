<?php

namespace App\Service;

use App\Http\Clients\HttpClient;
use App\Helpers\ResponseHelpers;
use Illuminate\Http\File;
use Illuminate\Http\Request;

class UploadService
{
    /// Fetch User (From Auth Service) details by User ID.
    public static function uploadfile( $file, string $asset_type)
    {
        return ["message" => "File sent"];
    }
}
