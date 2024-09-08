<?php

namespace App\Impl;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class LiveImpl
{
    private static mixed $cloudName;
    private static mixed $apiKey;
    private static mixed $apiSecret;

    // Static method to initialize Cloudinary credentials
    public static function initialize(): void
    {
        self::$cloudName = env("CLOUDINARY_CLOUD_NAME");
        self::$apiKey = env("CLOUDINARY_API_KEY");
        self::$apiSecret = env("CLOUDINARY_API_SECRET");
    }

    // Method to create a live stream using Cloudinary API

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public static function createLiveStream($streamName, $inputType = 'rtmp', $idleTimeout = 120, $maxRuntime = 43200)
    {
       self::initialize();

        $url = "https://api.cloudinary.com/v2/" . self::$cloudName . "/video/live_streams";
        $headers = self::buildHeaders();

        $data = [
            'name' => $streamName,
            'input' => [
                'type' =>  'rtmp'
            ],
            'idle_timeout_sec' => $idleTimeout,
            'max_runtime_sec' => $maxRuntime
        ];

        $response = Http::withHeaders($headers)->post($url, $data);

        return self::handleResponse($response);
    }

    // Method to activate a live stream

    /**
     * @throws ConnectionException
     */
    public static function activateLiveStream($streamId)
    {
        self::initialize();

        $url = "https://api.cloudinary.com/v2/" . self::$cloudName . "/video/live_streams/{$streamId}/activate";
        $headers = self::buildHeaders();

        $response = Http::withHeaders($headers)->post($url);

        return self::handleResponse($response);
    }

    // Method to set a live stream to idle

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public static function setLiveStreamToIdle($streamId)
    {
        self::initialize();

        $url = "https://api.cloudinary.com/v2/" . self::$cloudName . "/video/live_streams/{$streamId}/idle";
        $headers = self::buildHeaders();

        $response = Http::withHeaders($headers)->post($url);

        return self::handleResponse($response);
    }

    // Method to delete a live stream

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public static function deleteLiveStream($streamId)
    {
        self::initialize();

        $url = "https://api.cloudinary.com/v2/" . self::$cloudName . "/video/live_streams/{$streamId}";
        $headers = self::buildHeaders();

        $response = Http::withHeaders($headers)->delete($url);

        return self::handleResponse($response);
    }

    // Method to create an output for simulcasting

    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public static function createLiveStreamOutput($streamId, $name, $type, $uri, $streamKey, $vendor)
    {
        self::initialize();

        $url = "https://api.cloudinary.com/v2/" . self::$cloudName . "/video/live_streams/{$streamId}/outputs";
        $headers = self::buildHeaders();

        $data = [
            'name' => $name,
            'type' => $type,
            'uri' => $uri,
            'stream_key' => $streamKey,
            'vendor' => $vendor
        ];

        $response = Http::withHeaders($headers)->post($url, $data);
        return self::handleResponse($response);
    }

    // Private static method to build the headers
    private static function buildHeaders(): array
    {

        return [
            'Authorization' => 'Basic ' . base64_encode(self::$apiKey . ':' . self::$apiSecret),
            'Content-Type' => 'application/json',
        ];
    }

    // Private static method to handle the response and check for errors

    /**
     * @throws \Exception
     */
    private static function handleResponse($response)
    {
        if ($response->failed()) {
            throw new \Exception('Live Stream Request failed with status: ' . $response->status() . ', ' . $response->body());
        }

        return $response->json();
    }
}
