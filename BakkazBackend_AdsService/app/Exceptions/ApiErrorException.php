<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiErrorException extends Exception
{
    protected $message;
    protected $statusCode;

    /**
     * Constructor for ApiErrorException.
     *
     * @param string $message
     * @param int $statusCode
     */
    public function __construct(string $message, int $statusCode = 400)
    {
        parent::__construct($message, $statusCode);
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => true,
            'message' => $this->message,
        ], $this->statusCode);
    }
}
