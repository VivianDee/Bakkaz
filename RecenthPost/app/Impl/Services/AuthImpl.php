<?php

namespace App\Impl\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthImpl
{
    private static string $baseUrl = "https://auth-ms.bakkaz.com";
    // private static string $baseUrl = "http://127.0.0.1:8001";
    private static string $service = "recenth-posts-service";

    ///Fetch User (From Auth Service) details by User ID.
    public static function getUserDetails(int $user_id, bool $fullData =  false)
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

        $visibility  = PreferenceImpl::getSinglePrivacySetting($user_id) ?? null;

        return $fullData ? $response->json("data.user") : [
            "id" => $res["id"],
            "first_name" => $res["first_name"],
            "last_name" => $res["last_name"],
            "name" => $res["name"],
            "email" => $res["email"],
            "country" => $res["country"],
            "visibility" => $visibility
        ];
    }

    ///Fetch User (From Auth Service) details by User ID.
    public static function getUsers()
    {
        $url = self::$baseUrl . "/api/user";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->get($url);

        if ($response->failed() || !$response->json("status")) {
            return null;
        }

        $res = $response->json("data");


        return $res;
    }

    ///Fetch User (From Auth Service) details by User ID.
    public static function getUsersByIds(array $user_ids)
    {
        $url = self::$baseUrl . "/api/user";

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

        // Get user data from response
        $users = $response->json("data.user");


        $privacy_settings = PreferenceImpl::getPrivacySettings($user_ids);

        // Use map to add visibility data to each user
        $usersWithVisibility = collect($users)->map(function ($user)
        use ($privacy_settings) {
            if (isset($user['id'])) {
                $user['visibility'] = $privacy_settings[$user['id']] ?? null;
            }
            return $user;
        });

        return ["data" => $usersWithVisibility->all()];
    }

    ///Filter User (From Auth Service) details by search paramnters.
    public static function FilterUserAccounts(string $searchParam)
    {
        $url = self::$baseUrl . "/api/user?search={$searchParam}&paginate=true";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
            "Authorization" =>
            "Bearer 64|aGsSvYbz77RD170mi64u20nVezVOcxLy4YgQTLyFf7fa33cd",
        ];

        $response = Http::withHeaders($headers)->get($url);

        if ($response->failed() || !$response->json("status")) {
            return null;
        }

        // Get user data from response
        $users = $response->json("data.users");

        // Extract all user IDs
        $user_ids = collect($users['data'])->pluck('id')->all();

        $privacy_settings = PreferenceImpl::getPrivacySettings($user_ids);

        // Use map to add visibility data to each user
        $usersWithVisibility = collect($users['data'])->map(function ($user)
        use ($privacy_settings) {
            if (isset($user['id'])) {
                $user['visibility'] = $privacy_settings[$user['id']] ?? null;
            }
            return $user;
        });

        return ["data" => $usersWithVisibility->all()];
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
    public static function uploadAsset(
           string $asset_type,
           array $files = [],
           string $uploadtype = 'Files',
           array $mediaFilesUrls = []
       ): array {
           $url = self::$baseUrl . "/api/asset/group";

           $headers = [
               "Cache-Control" => "no-cache",
               "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
               "service" => self::$service,
           ];

           $request = Http::withHeaders($headers);

           try {
               // Attach files or URLs based on upload type
               if ($uploadtype === 'Urls') {
                   foreach ($mediaFilesUrls as $mediaFilesUrl) {
                       $request->attach('media_files_urls[]', $mediaFilesUrl);
                   }
               } else {
                   foreach ($files as $filePath) {
                       if (file_exists($filePath)) {
                           $request->attach('media_files[]', fopen($filePath, 'r'), basename($filePath));
                       } else {
                           throw new \Exception("File not found: $filePath");
                       }
                   }
               }

               $response = $request->post($url, [
                   "asset_type" => $asset_type,
               ]);

               if ($response->successful()) {
                   return $response->json(); // Return response in JSON format
               }

               Log::error('Asset upload failed with status ' . $response->status() . ': ' . $response->body());
               return [
                   'status' => false,
                   'message' => 'Asset upload failed. Please check the logs for more details.',
               ];

           } catch (\Exception $e) {
               Log::error('Asset upload failed: ' . $e->getMessage());
               return [
                   'status' => false,
                   'message' => 'Asset upload failed. Please try again later.',
               ];
           }
       }

    public static function sendActionNotification(
        string $notification_type,
        array $data
    ) {
        $url = self::$baseUrl . "/api/notification/send";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $request = Http::withHeaders($headers);

        $payload = [
            "notification_type" => $notification_type,
        ];

            switch ($notification_type) {
                case "favourite":
                    $payload["recipient_id"] = $data["recipient_id"];
                    $payload["initiator_id"] = $data["initiator_id"];
                    $payload["status"] = $data["status"];
                    break;
                case "like":
                case "comment":
                    // Example payload for like/comment notification
                    $payload["recipient_id"] = $data["recipient_id"];
                    $payload["initiator_id"] = $data["initiator_id"];
                    $payload["post_id"] = $data["post_id"];
                    break;
                case "reply":
                    // Example payload for reply notification
                    $payload["recipient_id"] = $data["recipient_id"];
                    $payload["initiator_id"] = $data["initiator_id"];
                    $payload["post_id"] = $data["post_id"];
                    $payload["comment_id"] = $data["comment_id"];
                    break;
                case "general":
                    // Example payload for general custom notification
                    $payload["recipient_id"] = $data["recipient_id"];
                    $payload["title"] = $data["title"];
                    $payload["body"] = $data["body"];
                    break;
                case "favourites_posts":
                    // Example payload for like/comment notification
                    $payload["recipients"] = $data["recipients"];
                    $payload["initiator_id"] = $data["initiator_id"];
                    $payload["post_id"] = $data["post_id"];
                    $payload["body"] = $data["body"];
                    break;
                default:
                    throw new Exception("Invalid notification type");
            }

            $response = $request->post($url, $payload);

            return $response;

    }

    public static function FilterUserAccountsForSortPost(string $searchParam, int $perPage = 15, int $page = 1)
    {
        $url = self::$baseUrl . "/api/user?search={$searchParam}&paginate=true&per_page={$perPage}&page={$page}";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
            "Authorization" => "Bearer 64|aGsSvYbz77RD170mi64u20nVezVOcxLy4YgQTLyFf7fa33cd",
        ];

        $response = Http::withHeaders($headers)->get($url);

        if ($response->failed() || !$response->json("status")) {
            return null;
        }

        // Extract pagination data and users
        $data = $response->json("data.users");
        $pagination = [
            'current_page' => $data['current_page'] ?? 1,
            'per_page' => $data['per_page'] ?? $perPage,
            'total' => $data['total'] ?? 0,
            'last_page' => $data['last_page'] ?? 1,
            'first_page_url' => $data['first_page_url'] ?? null,
            'last_page_url' => $data['last_page_url'] ?? null,
            'next_page_url' => $data['next_page_url'] ?? null,
            'prev_page_url' => $data['prev_page_url'] ?? null,
            'path' => $data['path'] ?? null,
        ];

        return [
            'users' => $data['data'] ?? [],
            'pagination' => $pagination
        ];
    }

    public static function updateMutualFavourites(int $user_id, int $ref_id, int $status)
    {
        $url = self::$baseUrl . "/api/notification/mutual_favourite";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $data = [
            "user_id" => $user_id,
            "ref_id" => $ref_id,
            "status" => $status
        ];

        $response = Http::withHeaders($headers)->post($url, $data);

        // Extract the status from the response
        $status = $response->json("status");
        $message = $response->json("message");

        return ["status" => $status, "message" => $message];
    }
}
