<?php

namespace App\Impl\Services;

use Illuminate\Support\Facades\Http;

class PreferenceImpl
{
    private static string $baseUrl = "http://prefs-ms.bakkaz.com";
    private static string $service = "preference-service";

    public static function createPreference(int $user_id, int $language_id)
    {
        $url = self::$baseUrl . "/api/preferences/create";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->post($url, [
            "user_id" => $user_id,
            "language" => $language_id,
        ]);
        if ($response->failed() || !$response->json("status")) {
            return null;
        }

        $res = $response->json("status");

        return $res;
    }
}
