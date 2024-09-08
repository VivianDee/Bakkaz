<?php

namespace App\Impl\Services;

use Illuminate\Support\Facades\Http;

class PreferenceImpl
{
    private static string $baseUrl = "http://prefs-ms.bakkaz.com";
    // private static string $baseUrl = "http://127.0.0.1:8001";
    private static string $service = "recenth-posts-service";

    public static function updateSubscritionStatus(int $user_id, bool $status, int $subscription_id = 0)
    {
        $url = self::$baseUrl . "/api/preferences/subscription";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $data = [
            "user_id" => $user_id,
            "subscribed" => $status,
            "subscription_id" => $subscription_id
        ];

        $response = Http::withHeaders($headers)->patch($url, $data);

        // Extract the status from the response
        $status = $response->json("status");
        $message = $response->json("message");

        return ["status" => $status, "message" => $message];
    }


    public static function getBlockedUsers(int $user_id)
    {
        $url = self::$baseUrl . "/api/preferences/privacy/blocked_users/{$user_id}";

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


    public static function getNonMentionableUsers()
    {
        $url = self::$baseUrl . "/api/preferences/privacy/non_mentionables";

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


    public static function searchCustomId(string $searchParam)
    {
        $url = self::$baseUrl . "/api/preferences/custom_id/search?search={$searchParam}";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->get($url);

        if ($response->failed() || !$response->json("status")) {
            return null;
        }

        $data = $response->json("data");

        $result = collect($data)->map(function ($item) {
            return $item['preference']['user_id'];
        });

        return $result;
    }
    

    public static function getPrivacySettings(array $user_ids)
    {
        $url = self::$baseUrl . "/api/preferences/privacy/show";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->post($url, [
            "user_ids" => $user_ids,
        ]);


        if ($response->failed() || !$response->json("status")) {
            return null;
        }

        return $response->json("data");
    }

    public static function getSinglePrivacySetting(int $user_id): mixed
    {
        $url = self::$baseUrl . "/api/preferences/privacy/{$user_id}";

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

