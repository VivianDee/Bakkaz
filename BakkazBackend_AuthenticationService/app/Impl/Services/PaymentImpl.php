<?php

namespace App\Impl\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class PaymentImpl
{
    private static string $baseUrl = "http://payment-ms.bakkaz.com";
    // private static string $baseUrl = "http://127.0.0.1:8002";
    private static string $service = "recent-posts-service";

    public static function initialzePayment(Request $request)
    {
        $url = self::$baseUrl . "/api/payment/initialize_transaction";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->post($url, $request->all());

        if ($response->failed() || !$response->json("status")) {
            return null;
        }

        return $response;
    }

    public static function verifyPayment(string $reference)
    {
        $url = self::$baseUrl . "/api/payment/verify?reference={$reference}";

        $headers = [
            "Cache-Control" => "no-cache",
            "x-api-key" => 'd7J$kLz1p@Gm4xQw9!R6nYb2^T8vEWq0Z*fOj5L&yHgX3rU',
            "service" => self::$service,
        ];

        $response = Http::withHeaders($headers)->get($url);

        // Extract the status from the response
        $status = $response->json("data.status");
        $message = $response->json("message");

        return ["status" => $status, "message" => $message];
    }

    public static function paymenthistory()
    {
        return [];
    }
}
