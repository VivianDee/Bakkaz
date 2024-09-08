<?php

namespace App\Helpers;

class HttpClient
{
    /**
     *  Makes a GET request to the specified URL
     *
     *  @param string $url The URL to send the request to
     *  @param array $headers Optional headers to include in the request
     *
     *  @return mixed The decoded response from the server (usually JSON)
     *  @throws \Exception If an error occurs during the request
     */
    public function get(string $url, array $headers = [])
    {
        $response = $this->sendRequest($url, "GET", $headers);
        return json_decode($response);
    }

    /**
     *  Makes a POST request to the specified URL
     *
     *  @param string $url The URL to send the request to
     *  @param array $data The data to send in the request body (usually JSON)
     *  @param array $headers Optional headers to include in the request
     *
     *  @return mixed The decoded response from the server (usually JSON)
     *  @throws \Exception If an error occurs during the request
     */
    public function post(string $url, array $data, array $headers = [])
    {
        $headers = array_merge($headers, [
            "Content-Type" => "application/json",
        ]);
        $response = $this->sendRequest($url, "POST", $headers, $data);
        return json_decode($response);
    }

    /**
     *  Sends a generic HTTP request (can be extended for other methods)
     *
     *  @param string $url The URL to send the request to
     *  @param string $method The HTTP method (GET, POST, etc.)
     *  @param array $headers Optional headers to include in the request
     *  @param string $data Optional data to send in the request body
     *
     *  @return string The raw response from the server
     *  @throws \Exception If an error occurs during the request
     */
    private function sendRequest(
        string $url,
        string $method,
        array $headers = [],
        array $data = []
    ) {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        if ($data) {
            $fields_string = http_build_query($data);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $fields_string);
        }

        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new \Exception("Error: " . $error);
        }

        return $response;
    }
}
