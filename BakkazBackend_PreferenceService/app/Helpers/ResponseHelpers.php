<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

abstract class ResponseHelpers
{

    public static function success(
        array $data = [],
        string $message = "Successful",
        int $statusCode = 200
    ): JsonResponse {
        return self::buildResponse(true, $message, $data, $statusCode);
    }

    public static function error(
        string $message = "An error occurred",
        int $statusCode = 400
    ): JsonResponse {
        return self::buildResponse(false, $message, [], $statusCode);
    }

    public static function created(
        string $message = "Resource created",
        int $statusCode = 201
    ): JsonResponse {
        return self::buildResponse(true, $message, [], $statusCode);
    }

    public static function updated(
        string $message = "Resource updated",
        int $statusCode = 200
    ): JsonResponse {
        return self::buildResponse(true, $message, [], $statusCode);
    }

    public static function internalServerError(
        string $message = "Internal Server Error",
        int $statusCode = 500
    ): JsonResponse {
        return self::buildResponse(false, $message, [], $statusCode);
    }

    public static function unauthorized(
        string $message = "Unauthorized",
        int $statusCode = 401
    ): JsonResponse {
        return self::buildResponse(false, $message, [], $statusCode);
    }

    public static function forbidden(
        string $message = "Forbidden",
        int $statusCode = 403
    ): JsonResponse {
        return self::buildResponse(false, $message, [], $statusCode);
    }

    public static function notFound(
        string $message = "Resource not found",
        int $statusCode = 404
    ): JsonResponse {
        return self::buildResponse(false, $message, [], $statusCode);
    }

    public static function gone(
        string $message = "Resource not found",
        int $statusCode = 410
    ): JsonResponse {
        return self::buildResponse(false, $message, [], $statusCode);
    }

    public static function conflict(
        string $message = "The record already exists.",
        int $statusCode = 409
    ): JsonResponse {
        return self::buildResponse(false, $message, [], $statusCode);
    }

    public static function unprocessableEntity(
        string $message = "Unprocessable Entity",
        int $statusCode = 422
    ): JsonResponse {
        return self::buildResponse(false, $message, [], $statusCode);
    }

    private static function buildResponse(
        bool $status,
        string $message,
        array $data = [],
        int $statusCode = 200
    ): JsonResponse {
        return response()->json(
            [
                "status" => $status,
                "statusCode" => $statusCode,
                "message" => $message,
                "data" => $data,
            ],
            $statusCode
        );
    }

    static function sendResponse(array $data = [], bool $status = true, int $statusCode = 200, string $message = 'Successful')
    {
        return response()->json([
            'status' => $status,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
        ]);
    }

    static function implodeNestedArray(array $data, array $keys, string $separator = ' '): string
    {
        $result = [];

        foreach ($keys as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                $result[] = implode($separator, $data[$key]);
            }
        }

        $results = $result[0];

        return $results;
    }

    static function errorResponse(string $message, int $statusCode)
    {
        return response()->json([
            'status' => false,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => [],
        ]);
    }

    static function successResponse(array $data)
    {
        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'message' => 'Successful',
            'data' => $data,
        ]);
    }
}
