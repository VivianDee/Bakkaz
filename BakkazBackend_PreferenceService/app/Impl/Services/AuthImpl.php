<?php

namespace App\Impl\Services;

use Illuminate\Support\Facades\Http;

class AuthImpl
{
    private static string $baseUrl = "https://auth-ms.bakkaz.com";
    // private static string $baseUrl = "http://127.0.0.1:8000";
    private static string $service = "preference-service";
    private static string $authorization = "Bearer 64|aGsSvYbz77RD170mi64u20nVezVOcxLy4YgQTLyFf7fa33cd";

    ///Fetch User (From Auth Service) details by User ID.
    public static function getUserDetails(int $user_id)
    {
        $url = self::$baseUrl . "/api/user/{$user_id}";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->get($url);

        if ($response->failed() || !$response->json("status")) {
            return null;
        }

        $res = $response->json("data.user");

        return [
            "first_name" => $res["first_name"],
            "last_name" => $res["last_name"],
            "name" => $res["name"],
            "email" => $res["email"],
            "country" => $res["country"],
            "user_deleted" =>  $res["deleted"],
            "active_status" =>  $res["active_status"]
        ];
        return $res;
    }

    ///Filter User (From Auth Service) details by search paramnters.
    //  public static function FilterUserAccounts(string $searchParam, int $page, int $perPage)
    public static function FilterUserAccounts(string $searchParam)
    {
        // $url = self::$baseUrl . "/api/user?search={$searchParam}&per_page={$perPage}&page={$page}";
        $url = self::$baseUrl . "/api/user?search={$searchParam}";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
            "Authorization" => self::$authorization,
        ];

        $response = Http::withHeaders($headers)->get($url);

        if ($response->failed() || !$response->json("status")) {
            return null;
        }

        return $response->json("data.users");
    }

    ///Fetch Post Asset (From Auth Service) details by Grouped Asset Id.
    public static function getGroupedAsset(string $group_id)
    {
        $url = self::$baseUrl . "/api/asset/group/{$group_id}";

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

    public static function getAllCountries()
    {
        $url = self::$baseUrl . "/api/resources/countries";

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

    public static function getAllCategories()
    {
        $url = self::$baseUrl . "/api/resources/categories";

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

    public static function uploadAsset(array $files, string $asset_type)
    {
        $url = self::$baseUrl . "/api/asset/group";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $request = Http::withHeaders($headers);

        foreach ($files as $filePath) {
            $request->attach(
                "media_files[]",
                file_get_contents($filePath),
                basename($filePath)
            );
        }

        $response = $request->post($url, [
            "asset_type" => $asset_type,
        ]);
        return $response;
    }

    public static function updateUserProfile(int $user_id, array $data)
    {
        $url = self::$baseUrl . "/api/user/{$user_id}";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
            "Authorization" => self::$authorization,
        ];

        $request = Http::withHeaders($headers);

        $response = $request->patch($url, $data);

        return $response->json();
    }
}
