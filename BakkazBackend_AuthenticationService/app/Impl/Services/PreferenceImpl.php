<?php

namespace App\Impl\Services;

use Illuminate\Support\Facades\Http;

class PreferenceImpl
{
    private static string $baseUrl = "http://prefs-ms.bakkaz.com";
//     private static string $baseUrl = "http://127.0.0.1:8001";
    private static string $service = "auth-service";

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

    public static function getStats()
    {
        $url = self::$baseUrl . "/api/preferences/stats";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->get($url);

         if ($response->failed() || !$response->json("status")) {
            return [];
        }

        return $response->json("data");
    }
    public static function checkIfBlocked($user_id, $otherUserId):bool
    {
        $url = self::$baseUrl . "/api/preferences/privacy/blocked_users/" . $user_id;


        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->get($url);


        if ($response->failed() || !$response->json("status")) {
            return false; // If the API call fails or the status is not true, assume the user is not blocked
        }

        $blockedUsers = $response->json("data");

        // Check if the otherUserId is in the blocked users list
        foreach ($blockedUsers as $blockedUser) {
            if ($blockedUser['blocked_user_id'] == $otherUserId) {
                return true; // User is blocked
            }
        }

        return false; // User is not blocked
    }

    public static function getNotificationSettings(int $user_id)
    {
        $url = self::$baseUrl . "/api/preferences/notification_settings/{$user_id}";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->get($url);

        if ($response->failed() || !$response->json("status")) {
            return [];
        }

        return $response->json("data")[0];
    }

}
