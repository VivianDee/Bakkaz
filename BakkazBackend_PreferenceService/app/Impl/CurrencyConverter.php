<?php

namespace App\Impl;

use GuzzleHttp\Client;

class CurrencyConverter
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;
    protected $apiUrlTwo;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('EXCHANGE_RATE_API_KEY'); // Set this in your .env file
        $this->apiUrl = 'https://v6.exchangerate-api.com/v6/';
        $this->apiUrlTwo = 'https://cdn.jsdelivr.net/npm/@fawazahmed0/currency-api@latest/v1/currencies/usd.json';
    }

    public function convert($amount, $fromCurrency, $toCurrency)
    {
        $rate = $this->getRateFromApi($fromCurrency, $toCurrency);

        if (!$rate) {
            throw new \Exception("Unable to fetch exchange rate for {$toCurrency}");
        }

        return $amount * $rate;
    }

    protected function getRateFromApi($fromCurrency, $toCurrency)
    {
        $url = $this->apiUrl . "{$this->apiKey}/latest/{$fromCurrency}";

        $response = $this->client->get($url);
        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['conversion_rates'][$toCurrency])) {
            return $data['conversion_rates'][$toCurrency];
        }

        // If the first API doesn't have the rate, fallback to the second API
        $url = $this->apiUrlTwo;
        $response = $this->client->get($url);
        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data[strtolower($fromCurrency)][strtolower($toCurrency)])) {
            return $data[strtolower($fromCurrency)][strtolower($toCurrency)];
        }

        return null;
    }
}
