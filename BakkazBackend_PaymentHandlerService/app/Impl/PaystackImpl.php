<?php

namespace App\Impl;

use App\Interfaces\PaymentImplInterface;
use App\Http\Clients\HttpClient;
use Exception;


class PayStackImpl implements PaymentImplInterface
{


    private $baseUrl = "https://api.paystack.co";
    private $client;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     *  This function resolves a bank account number using the PayStack API
     *
     *  @param string $secretKey Your PayStack secret key
     *  @param string $accountNumber The bank account number to resolve
     *  @param string $bankCode The bank code associated with the account number
     *
     *  @return mixed The response from the PayStack API (decoded JSON)
     *  @throws Exception If an error occurs during the API call
     */
    public function resolveAccountNumber(string $secretKey, string $accountNumber, string $bankCode)
    {
        $url = "/bank/resolve?account_number=" . $accountNumber . "&bank_code=" . $bankCode;

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }


    public function getBanks(string $secretKey)
    {
        // country=nigeria
        // country=ghana

        $url = "/bank?country=nigeria";

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }

    // Initialize a Transaction
    public function initializeTransaction(string $secretKey, string $email, string $amount, array $metadata, string $currency)
    {
        $url = "/transaction/initialize";


        $data = [
            "amount" => $amount,
            "email" => $email,
            "metadata" => $metadata,
            "currency" => $currency
        ];

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $data, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response;
        }

        return $response;
    }




    // Verify Transaction payment status
    public function verifyTransaction(string $secretKey, string $reference)
    {
        $url = "/transaction/verify/{$reference}";

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }


    // Get all Transaction
    public function showTransactions(string $secretKey)
    {
        $url = "/transaction";

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }

    // Get Transaction by Transaction ID
    public function showTransactionById(string $secretKey, int $id)
    {
        $url = "/transaction/{$id}";

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }

    // Show Transaction Timeline by
    public function showTransactionTimeline(string $secretKey, string $reference)
    {
        $url = "/transaction/timeline/{$reference}";

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response;
        }

        return $response;
    }



    // Create A  Receipient For a Transfer
    public function createRecipient(string $secretKey, string $type, string $name, string $account_number, string $bank_code, string $currency = "NGN")
    {
        $url = "/transferrecipient";

        $data = [
            "type" => $type,
            "name" => $name,
            "account_number" => $account_number,
            "bank_code" => $bank_code,
            "currency" => $currency

        ];

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $data, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }


    // Fetch A  Receipient For a Transfer
    public function FetchRecipient(string $secretKey, string $bank_code, string $account_number)
    {
        $url = "/transferrecipient";

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response;
        }


        // Extract account numbers and bank codes to separate arrays
        $account_numbers = array_column(array_map(function ($item) {
            return $item->details;
        }, $response->data), 'account_number');

        $bank_codes = array_column(array_map(function ($item) {
            return $item->details;
        }, $response->data), 'bank_code');

        // Find the index of the matching recipient
        $index = array_search([$account_number, $bank_code], array_map(null, $account_numbers, $bank_codes));

        $recipient = $index !== false ? $response->data[$index] : null;


        return $recipient;
    }

    // Initiate inter bank Transfer
    public function initiateTransfer(string $secretKey, string $amount, string $recipient)
    {
        $url = "/transfer";

        $data = [
            "source" => "balance",
            "amount" => $amount,
            "recipient" => $recipient,
        ];

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $data, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response;
        }

        return $response;
    }

     // Initiate Bulk Inter Bank Transfer
     public function initiateBulkTransfer(string $secretKey, array $transfers)
     {
         $url = "/transfer";
 
         $data = [
             "source" => "balance",
             "currency" => "NGN",
             "transfers" => $transfers
         ];
 
         $headers = [
             "Authorization: Bearer " . $secretKey,
             "Cache-Control: no-cache",
         ];
 
         $response = $this->client->post($this->baseUrl . $url, $data, $headers);
 
         if (isset($response->status) && $response->status !== true) {
             return  $response;
         }
 
         return $response;
     }



     // Finalize a transfer / Verification of OTP
    public function finalizeTransfer(string $secretKey, string $transfer_code, string $otp)
    {
        $url = "/transfer/finalize_transfer";

        $data = [
            "transfer_code" => $transfer_code,
            "otp" => $otp
        ];

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $data, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response;
        }

        return $response;
    }

    // Create a Paystack Customer
    public function createCustomer(string $secretKey, string $email, string $first_name, string $last_name, string $phone = null)
    {
        $url = "/customer";

        $data = [
            "email" => $email,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "phone" => $phone
        ];

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $data, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }


    // Update a Paystack Customer's Information 
    public function updateCustomer(string $secretKey, string $email, string $first_name, string $last_name, string $phone = null, int $customer_code)
    {
        $url = "/customer/{$customer_code}";



        $data = [
            "email" => $email,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "phone" => $phone
        ];

        $filtered_data = array_filter($data, function ($value) {
            return !is_null($value) && $value !== '';
        });


        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $filtered_data, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }

    // Fetch Customer
    public function Fetchcustomer($secretKey,  $email)
    {
        $url = "/customer/{$email}";

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response;
        }

        return $response;
    }


    // Create a Dedicated Virtual Account for an Existing Customer

    public function createCustomerDVA(string $secretKey, string $customer, string $preferred_bank)
    {
        $url = "/dedicated_account";

        //"wema-bank"
        //"titan-bank"

        //"test-bank"


        $data = [
            "customer" => $customer,
            "preferred_bank" => $preferred_bank
        ];

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $data, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }


    // Create a Customer and Dedicated Virtual Account

    public function createCustomerAndCustomerDVA(string $secretKey, string $email, string $first_name, string $middle_name, string $last_name, string $phone, string $preferred_bank)
    {
        $url = "/dedicated_account";

        $data = [
            "email" => $email,
            "first_name" => $first_name,
            "middle_name" => $middle_name,
            "last_name" => $last_name,
            "phone" => $phone,
            "preferred_bank" => $preferred_bank,
            "country" => "NG"
        ];

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $data, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }


    // Get all Customers
    public function getAllCustomers(string $secretKey)
    {
        $url = "/customer";

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url,  $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }

    /// Charge Authorization for reoccuring payments (Subscriptions)
    public function chargeAuthorization(string $secretKey, string $email, int $amount, string $authorization_code)
    {
        $url = "/transaction/charge_authorization";

        $data = [
            "email" => $email,
            "amount" => $amount,
            "authorization_code" => $authorization_code
        ];

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $data, $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }


    // Initiate a Refund
    public function Refund(string $secretKey, string $reference, string $amount = null)
    {
        $url = "/refund";

        $data = [
            "transaction" => $reference,
            "amount" => $amount,
        ];

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->post($this->baseUrl . $url, $data, $headers);

        return $response;
    }

    // Show Refunds
    public function showRefunds(string $secretKey)
    {
        $url = "/refund";

        $headers = [
            "Authorization: Bearer " . $secretKey,
            "Cache-Control: no-cache",
        ];

        $response = $this->client->get($this->baseUrl . $url,  $headers);

        if (isset($response->status) && $response->status !== true) {
            return  $response->message;
        }

        return $response;
    }
}
