<?php

namespace App\Interfaces;

interface DigitalOceanCdnInterface
{
    static public function purge($fileName);

    static public function uploadImage($realPath, $sub_folder = null);

    static public function hardDeleteImage($fileName, $sub_folder = null);

    static public function hardUpdateImage($realPath, $sub_folder = null, $fileName);

    static public function determineFileType($filePath);
    
}