<?php

namespace App\Impl\Services;

use Illuminate\Support\Facades\Http;

class RecenthPostImpl
{
    private static string $baseUrl = "https://recenth-post-app.bakkaz.com";
    // private static string $baseUrl = "http://127.0.0.1:8009";
    private static string $service = "preference-service";
    private static string $authorization = "Bearer 64|aGsSvYbz77RD170mi64u20nVezVOcxLy4YgQTLyFf7fa33cd";

    public static function getMutualFavorites(int $user_id, int $ref_id)
    {
        $url = self::$baseUrl . "/api/mutual_favorite?user_id={$user_id}&ref_id={$ref_id}";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
            "Authorization" => self::$authorization,
        ];

        $request = Http::withHeaders($headers);

        $response = $request->get($url);

        return $response->json("data");
    }
}
