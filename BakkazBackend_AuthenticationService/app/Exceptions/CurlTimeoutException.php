<?php

namespace App\Exceptions;

use Exception;

class CurlTimeoutException extends Exception
{
    public function __construct(
        $message = "cURL timeout error",
        $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json(
            [
                "error" => "CURL_TIMEOUT",
                "message" => $this->getMessage(),
            ],
            504
        );
    }
}
