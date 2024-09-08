<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface PaymentImplInterface
{
    /// Resolves a bank account number using the PayStack API
    public function resolveAccountNumber(string $secretKey, string $accountNumber, string $bankCode);

    /// Get Banks
    public function getBanks(string $secretKey);




    /// Initialize a Transaction
    public function initializeTransaction(string $secretKey, string $email, string $amount, array $metadata, string $currency);

    /// Verify Transaction Payment Status
    public function verifyTransaction(string $secretKey, string $reference);

    /// Get Transaction
    public function showTransactions(string $secretKey);

    // Fetch A  Receipient For a Transfer
    public function FetchRecipient(string $secretKey, string $bank_code, string $account_number);
   
    /// Get Transaction by Transaction ID
    public function showTransactionById(string $secretKey, int $id);

    /// Show Transaction Timeline by
    public function showTransactionTimeline(string $secretKey, string $reference);







    /// Create A  Receipient For a Transfer
    public function createRecipient(string $secretKey, string $name, string $account_number, string $bank_code, string $currency);

    // Initiate a Transfer
    public function initiateTransfer(string $secretKey, string $amount, string $recepient);

    // Finalize a Transfer
    public function finalizeTransfer(string $secretKey, string $amount, string $recepient);





    /// Create a Paystack Customer
    public function createCustomer(string $secretKey, string $email, string $first_name, string $last_name, string $phone);

    /// Update a Paystack Customer
    public function updateCustomer(string $secretKey, string $email, string $first_name, string $last_name, string $phone = null, int $customer_code);
   
    // Fetch Customer
    public function Fetchcustomer($secretKey,  $email);

    /// Create a Dedicated Virtual Account for an Existing Customer
    public function createCustomerDVA(string $secretKey, string $customer, string $preferred_bank);

    // Create a Customer and Dedicated Virtual Account
    public function createCustomerAndCustomerDVA(string $secretKey, string $email, string $first_name, string $middle_name, string $last_name, string $phone, string $preferred_bank);

    // Get all Customers
    public function getAllCustomers(string $secretKey);





    // Charge Authorization for reoccuring payments (Subscriptions)
    public function chargeAuthorization(string $secretKey, string $email, int $amount, string $authorization_code);








    // Initiate Refund
    public function Refund(string $reference, string $amount);

    // Show Refunds
    public function showRefunds(string $secretKey);
}
