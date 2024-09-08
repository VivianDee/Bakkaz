<?php

namespace App\Services;

use App\Impl\PaystackImpl;
use App\Http\Clients\HttpClient;

class PaystackService
{

    // Initialize a transaction
    static public function InitializeTransaction(string $email, string $amount, array $metadata, string $currency,  bool $test=false)
    {
        $secret_key = $test ? env('PAYSTACK_SECRET_KEY_TEST') : env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->initializeTransaction(secretKey: $secret_key, email: $email, amount: $amount, metadata: $metadata, currency: $currency);

        return $response;
    }

    // Get all Paystack customers
    static public function getAllCustomers()
    {
        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->getAllCustomers(secretKey: $secret_key);

        return $response;
    }

    // Create a paystack customer
    static public function createCustomer(string $email, string $first_name, string $last_name)
    {
        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->createCustomer(secretKey: $secret_key, email: $email, first_name: $first_name, last_name: $last_name);

        return $response;
    }

    // Check if a paystack Customer already exists
    static public function customerExists(string $email)
    {

        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->Fetchcustomer(secretKey: $secret_key, email: $email);

        if (isset($response->status) && $response->status !== true) {
            return false;
        }

        return true;
    }


    // Show Transactions
    static public function showTransactions(int $id = null)
    {

        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        if ($id) {
            $response = $paystack->showTransactionById(secretKey: $secret_key, id: $id);

            return $response;
        }

        $response = $paystack->showTransactions(secretKey: $secret_key);

        return $response;
    }

    // Verify Transaction Status
    static public function verifyTransaction(string $reference, bool $test=false)
    {
        $secret_key = $test ? env('PAYSTACK_SECRET_KEY_TEST') : env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->verifyTransaction(secretKey: $secret_key, reference: $reference);

        return $response;
    }


    // Show Transaction Reference
    static public function showTransactionTimeline(string $reference)
    {
        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->showTransactionTimeline(secretKey: $secret_key, reference: $reference);

        return $response;
    }










    /// Transfers




    // Initialize transfer
    static public function initiateTransfer(string $amount, string $recipient)
    {
        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->initiateTransfer(secretKey: $secret_key, amount: $amount, recipient: $recipient);

        return $response;
    }


     // Finalize transfer
     static public function FinalizeTransfer(string $transfer_code, string $otp)
     {
         $secret_key = env('PAYSTACK_SECRET_KEY');
 
         $client = new HttpClient();
         $paystack = new PaystackImpl($client);
 
         $response = $paystack->FinalizeTransfer(secretKey: $secret_key, transfer_code: $transfer_code, otp: $otp);
 
         return $response;
     }


     // Initialize Bulk transfer
    static public function initiateBulkTransfer(array $transfers)
    {
        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->initiateBulkTransfer(secretKey: $secret_key, transfers: $transfers);

        return $response;
    }



    // Create a Receipient
    static public function createReceipient(string $type, string $name, string $account_number, string $bank_code)
    {
        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->createRecipient(secretKey: $secret_key, type: $type, name: $name, account_number: $account_number, bank_code: $bank_code);

        return $response;
    }


    // Check if a Receipient already exists
    static public function ReceipientExists(string $bank_code, string $account_number)
    {

        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->FetchRecipient(secretKey: $secret_key, bank_code: $bank_code, account_number: $account_number);

        return $response;
    }

    //List Banks and Bank codes
    static public function showBanks()
    {
        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->getBanks(secretKey: $secret_key);

        return $response;
    }













    // Refunds



    // Initiate Refund
    static public function initiateRefund(string $reference, string $amount)
    {
        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->Refund(secretKey: $secret_key, reference: $reference, amount: $amount);

        return $response;
    }

    // Show Refunds
    static public function showRefunds()
    {
        $secret_key = env('PAYSTACK_SECRET_KEY');

        $client = new HttpClient();
        $paystack = new PaystackImpl($client);

        $response = $paystack->showRefunds(secretKey: $secret_key);

        return $response;
    }
}
