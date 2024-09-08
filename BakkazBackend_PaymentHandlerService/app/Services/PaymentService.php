<?php

namespace App\Services;

use App\Enums\BakkazServiceType;
use App\Helpers\DateHelper;
use App\Helpers\ResponseHelpers;
use App\Impl\Services\AuthImpl;
use App\Interfaces\PaymentInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Services\PaystackService;
use App\Models\Payment;
use App\Models\PaymentUrl;
use App\Models\Refund;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PaymentService implements PaymentInterface
{
    /// Transactions
    public static function InitializeTransaction(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "amount" => 'required|string|regex:/^\d+(\.\d{1,2})?$/',
                "user_id" => "required|integer",
                "currency" => "sometimes|string",
                "metadata" => "sometimes|array",
                "metadata.payment_split" => "sometimes|array",
                "metadata.payment_split.*.app_name" =>
                "required_with:metadata.payment_split|string",
                "metadata.payment_split.*.percentage" =>
                "required_with:metadata.payment_split|numeric|min:0|max:100",
                "metadata.payment_split.*.amount" =>
                "required_with:metadata.payment_split|numeric|min:0",
                "mode" => "sometimes|string|in:test,live",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            // Check if payment splits exist and validate the total amount
            $amount = (float) $request->input('amount');
            $paymentSplits = $request->input('metadata.payment_split', []);
            $mode = $request->input('mode');
            $totalSplitAmount = 0;

            foreach ($paymentSplits as $split) {
                if (isset($split['amount'])) {
                    $totalSplitAmount += (float) $split['amount'];
                }
            }

            if (!empty($paymentSplits) && $totalSplitAmount !== $amount) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 400,
                    message: "The sum of all payment splits must equal the total amount."
                );
            }


            DB::beginTransaction();

            $user_id = $request->input("user_id");
            $service = $request->header("service");
            $currency = $request->input("currency", "NGN");

            // Get the user details from the Auth Service
            $user = AuthImpl::getUserDetails($user_id);

            if (!$user) {
                DB::rollBack();
                return ResponseHelpers::notFound(
                    message: "User Account Not found"
                );
            }

            $email = $user["email"];
            $first_name = $user["first_name"];
            $last_name = $user["last_name"];
            $amount = $request->input("amount");
            $metadata = $request->input("metadata", ["payment_split" => []]);

            if (!PaystackService::customerExists($email)) {
                $customer = PaystackService::createCustomer(
                    email: $email,
                    first_name: $first_name,
                    last_name: $last_name
                );
            }

            $transaction = PaystackService::InitializeTransaction(
                email: $email,
                amount: $amount * 100,
                metadata: $metadata,
                currency: $currency,
                test: $mode === "test" ? true : false,
            );

            if (!$transaction->status) {
                DB::rollBack();
                return ResponseHelpers::error(message: $transaction->message);
            }

            $payment = Payment::create([
                "user_id" => $user_id,
                "amount" => $amount,
                "currency" => "NGN",
                "status" => "Pending",
                "service_ref" => $service,
                "payment_reference" => $transaction->data->reference,
                "verified" => false,
            ]);

            // Save payment splits
            $paymentSplits = $metadata["payment_split"] ?? [];
            if (is_array($paymentSplits)) {
                foreach ($paymentSplits as $split) {
                    $payment->splits()->create([
                        "app_name" => $split["app_name"] ?? '',
                        "percentage" => $split["percentage"] ?? 0,
                        "amount" => $split["amount"] ?? 0,
                    ]);
                }
            }

            $payment->paymentUrl()->create([
                "payment_url" => $transaction->data->authorization_url,
                "expires_at" => DateHelper::addDays(days: 1, date: now()),
                "status" => "Active",
            ]);

            DB::commit();

            return ResponseHelpers::success(
                data: $payment->fresh()->load("paymentUrl", "splits")->toArray()
            );
        } catch (ValidationException $e) {
            DB::rollBack();
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "amount",
                    "user_id",
                    "currency"
                ])
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(message: $th);
        }
    }

    public static function verifyTransaction(Request $request)
    {
        try {
            $paymentReference =
                $request->query("reference") ?? $request->input("reference");

            $mode = $request->query("mode");

            $payment = Payment::where(
                "payment_reference",
                $paymentReference
            )->firstOrFail();

            $status = PaystackService::verifyTransaction(
                $paymentReference,
                $mode === "test" ? true : false,
            );

            $payment->update(["status" => $status->data->status]);

            DB::beginTransaction();

            if ($status->data->status === "success") {
                return self::handleSuccessfulTransaction($payment, $status);
            } elseif ($status->data->status === "reversed") {
                return self::handleReversedTransaction($payment);
            }

            DB::commit();
            return ResponseHelpers::success(
                message: $status->data->gateway_response,
                data: ["status" => $status->data->status]
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    public static function showPaystackTransactions(Request $request)
    {
        try {
            $transactions = PaystackService::showTransactions();

            if (!$transactions->status) {
                return ResponseHelpers::error(message: $transactions->message);
            }

            return ResponseHelpers::success(
                data: $transactions->data,
                message: "Transactions retrieved successfully"
            );
        } catch (\Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    public static function showTransactions(Request $request)
    {
        try {
            $service = $request->header("service");
            $user_id = $request->route("id");
            $payment_id = $request->query("payment_id");

            $query = Payment::query();

            if ($service) {
                $query->where("service_ref", $service);
            }

            if ($user_id) {
                $query->where("user_id", $user_id);
            }

            if ($payment_id) {
                $query->where("id", $payment_id);
            }

            $transactions = $query->get();

            if ($transactions->isEmpty()) {
                return ResponseHelpers::error(message: "No Transactions found");
            }

            return ResponseHelpers::success(
                data: $transactions
                    ->load("paymentUrl", "authorization", "splits")
                    ->toArray(),
                message: "Transactions retrieved successfully"
            );
        } catch (\Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    public static function showTransactionTimeline(Request $request)
    {
        try {
            $reference = $request->route("reference");

            if (!$reference) {
                return ResponseHelpers::error(
                    message: "Transaction reference is required."
                );
            }

            $transaction = PaystackService::showTransactionTimeline($reference);

            if (!$transaction->status) {
                return ResponseHelpers::error(message: $transaction->message);
            }

            $transactionData = (array) $transaction->data;

            return ResponseHelpers::success(
                data: $transactionData,
                message: "Transaction timeline retrieved successfully."
            );
        } catch (\Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    /// Transfers

    public static function initializeTransfer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "amount" => 'required|string|regex:/^\d+(\.\d{1,2})?$/',
                "type" => "required|string",
                "name" => "required|string",
                "account_number" => "required|string",
                "bank_code" => "required|string",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            DB::beginTransaction();

            $user_id = $request->input("user_id");
            $service = $request->header("service");

            // Get the user details from the Auth Service
            $user = AuthImpl::getUserDetails($user_id);

            if (!$user) {
                DB::rollBack();
                return ResponseHelpers::notFound(
                    message: "User Account Not found"
                );
            }

            $amount = ($request->input("amount") * 100);
            $type = $request->input("type");
            $name = $request->input("name");
            $account_number = $request->input("account_number");
            $bank_code = $request->input("bank_code");

            $recipient = PaystackService::ReceipientExists(
                account_number: $account_number,
                bank_code: $bank_code
            );

            if (!$recipient) {
                $recipient = PaystackService::createReceipient(
                    type: $type,
                    name: $name,
                    account_number: $account_number,
                    bank_code: $bank_code
                );
            }

            $transfer = PaystackService::initiateTransfer(
                amount: $amount,
                recipient: $recipient->recipient_code
            );

            if (!$transfer->status) {
                DB::rollBack();
                return ResponseHelpers::error(message: $transfer->message);
            }

            $transfer = Transfer::create([
                "user_id" => $user_id,
                "recipient_code" => $recipient->recipient_code,
                "amount" => $transfer->data->amount / 100,
                "currency" => "NGN",
                "type" => $recipient->type,
                "status" => $transfer->data->status,
                "transfer_code" => $transfer->data->transfer_code,
                "service_ref" => $service,
            ]);

            DB::commit();

            return ResponseHelpers::success(data: $transfer->toArray());
        } catch (ValidationException $e) {
            DB::rollBack();
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "amount",
                    "user_id",
                    "type",
                    "name",
                    "account_number",
                    "bank_code",
                ])
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(message: $th);
        }
    }

    public static function FinalizeTransfer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "otp" => "required|integer|min:3",
                "transfer_code" => "required|string",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            $otp = $request->input("otp");
            $transfer_code = $request->input("transfer_code");

            $transfer = Transfer::where(
                "transfer_code",
                $transfer_code
            )->first();

            if (!$transfer) {
                return ResponseHelpers::error(
                    message: "No Transfer History Found"
                );
            }

            $response = PaystackService::FinalizeTransfer(
                transfer_code: $transfer_code,
                otp: $otp
            );

            if (!$response->status) {
                return ResponseHelpers::error(
                    message: "Transfer Finalized Successfully"
                );
            }

            $transfer->update([
                "status" => $response->data->status,
            ]);

            return ResponseHelpers::success(data: $transfer->toArray());
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "transfer_code",
                    "otp",
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    public static function initializeBulkTransfer(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "recipients" => "required|array",
                "recipients.*.amount" => 'required|string|regex:/^\d+(\.\d{1,2})?$/',
                "recipients.*.user_id" => "required|integer",
                "recipients.*.type" => "required|string",
                "recipients.*.name" => "required|string",
                "recipients.*.account_number" => "required|string",
                "recipients.*.bank_code" => "required|string",
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            DB::beginTransaction();

            $recipients = $request->input("recipients");
            $service = $request->header("service");
            $transfers = [];
            $user_ids = [];

            foreach ($recipients as $recipientData) {
                $user_id = $recipientData["user_id"];

                // Get the user details from the Auth Service
                $user = AuthImpl::getUserDetails($user_id);

                if (!$user) {
                    DB::rollBack();
                    return ResponseHelpers::notFound(
                        message: "No User found for user_id: {$user_id}"
                    );
                }

                $amount = $recipientData["amount"] * 100;
                $type = $recipientData["type"];
                $name = $recipientData["name"];
                $account_number = $recipientData["account_number"];
                $bank_code = $recipientData["bank_code"];

                $recipient = PaystackService::ReceipientExists(
                    account_number: $account_number,
                    bank_code: $bank_code
                );

                if (!$recipient) {
                    $recipient = PaystackService::createReceipient(
                        type: $type,
                        name: $name,
                        account_number: $account_number,
                        bank_code: $bank_code
                    );
                }

                $transfers[] = [
                    "amount" => $amount,
                    "recipient" => $recipient->recipient_code,
                ];

                $user_ids[$recipient->recipient_code] = [
                    $user_id,
                    $recipient->type,
                ];
            }

            $bulk_transfers = PaystackService::initiateBulkTransfer(
                transfers: $transfers
            );

            if (!$bulk_transfers->status) {
                DB::rollBack();
                return ResponseHelpers::error(
                    message: $bulk_transfers->message
                );
            }

            $response = [];

            foreach ($bulk_transfers->data as $transfer) {
                $transfer_model = Transfer::create([
                    "user_id" => $user_ids[$transfer->recipient_code][0],
                    "recipient_code" => $transfer->recipient_code,
                    "amount" => $transfer->amount / 100,
                    "currency" => "NGN",
                    "type" => $user_ids[$transfer->recipient_code][1],
                    "status" => $transfer->status,
                    "transfer_code" => $transfer->transfer_code,
                    "service_ref" => $service . "/bulk_transfer",
                ]);

                if (!$transfer_model) {
                    $response[] = [
                        "message" => "Error Creating Transfer for User with ID: {$user_id}. Transfer_code{$transfer->transfer_code}",
                    ];
                } else {
                    $response[] = $transfer_model->toArray();
                }
            }

            DB::commit();

            return ResponseHelpers::success(data: $response);
        } catch (ValidationException $e) {
            DB::rollBack();
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "amount",
                    "user_id",
                    "type",
                    "name",
                    "account_number",
                    "bank_code",
                ])
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    // List Banks and Bank Codes
    public static function showBanks(Request $request)
    {
        $banks = PaystackService::showBanks();

        $banksData = (array) $banks->data;

        return ResponseHelpers::success(data: $banksData);
    }

    public static function showTransfers(Request $request)
    {
        try {
            $service = $request->header("service");
            $user_id = $request->route("id");

            $query = Transfer::query();

            if ($service) {
                $query->where("service_ref", $service);
            }

            if ($user_id) {
                $query->where("user_id", $user_id);
            }

            $transfers = $query->get();

            if ($transfers->isEmpty()) {
                return ResponseHelpers::error(message: "No Transfers found");
            }

            return ResponseHelpers::success(
                data: $transfers->toArray(),
                message: "Transfers retrieved successfully"
            );
        } catch (\Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    /// Refunds
    public static function initiateRefund(Request $request)
    {
        try {
            $request->validate([
                "amount" => 'required|string|regex:/^\d+(\.\d{1,2})?$/',
                "reference" => "required|string",
                "reason" => "sometimes|string",
            ]);

            DB::beginTransaction();

            $payment = Payment::where(
                "payment_reference",
                $request->input("reference")
            )->firstOrFail();

            $reference = $payment->payment_reference;
            $amount = $request->input("amount") * 100;

            $refund = PaystackService::initiateRefund(
                reference: $reference,
                amount: $amount
            );

            if (!$refund->status) {
                DB::rollBack();
                return ResponseHelpers::error(message: $refund->message);
            }

            $refund_model = $payment->refund()->create([
                "refund_reference" => $refund->data->transaction->reference,
                "amount" => $refund->data->amount / 100,
                "status" => $refund->data->status,
                "reason" => $request->input("reason", null),
                "processed_at" => now(),
            ]);

            DB::commit();

            return ResponseHelpers::success(
                data: $refund_model->toArray(),
                message: $refund->message
            );
        } catch (ValidationException $e) {
            DB::rollBack();
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    "amount",
                    "reference",
                ])
            );
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ResponseHelpers::error(message: "Payment not found.");
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(message: $th);
        }
    }

    public static function showPaystackRefunds(Request $request)
    {
        $refunds = PaystackService::showRefunds();

        if (isset($refunds->status) && $refunds->status !== true) {
            return ResponseHelpers::error(message: $refunds->message);
        }

        $refundsData = (array) $refunds->data;

        return ResponseHelpers::success(
            data: $refundsData,
            message: $refunds->message
        );
    }

    // Show Refunds
    public static function ShowRefunds(Request $request)
    {
        try {
            $refunds = Refund::all();

            if (!$refunds) {
                return ResponseHelpers::error(message: "No Refunds found");
            }

            return ResponseHelpers::success(
                data: $refunds->toArray(),
                message: "Refunds retrieved successfully"
            );
        } catch (\Exception $e) {
            return ResponseHelpers::internalServerError(
                message: $e->getMessage()
            );
        }
    }

    // Check for expired payment urls
    private static function checkExpiredPaymentUrls(int $duration)
    {
        $now = Carbon::now();

        $expiredLinks = PaymentUrl::where("status", "Active")
            ->where("expires_at", "<", $now->subMinutes($duration))
            ->get();

        foreach ($expiredLinks as $link) {
            $link->update(["status" => "Expired"]);
        }
    }

    // Handle Successful Transactions
    private static function handleSuccessfulTransaction(
        $payment,
        $transactionStatus
    ) {
        $authorization = $payment->authorization()->firstOrCreate(
            ["payment_id" => $payment->id],
            [
                "authorization_code" =>
                $transactionStatus->data->authorization->authorization_code,
                "card_type" =>
                $transactionStatus->data->authorization->card_type,
                "last4" => $transactionStatus->data->authorization->last4,
                "exp_month" =>
                $transactionStatus->data->authorization->exp_month,
                "exp_year" => $transactionStatus->data->authorization->exp_year,
                "bank" => $transactionStatus->data->authorization->bank,
            ]
        );

        $payment->paymentUrl()->delete();

        DB::commit();

        return ResponseHelpers::success(
            data: $payment->load("paymentUrl", "authorization")->toArray()
        );
    }

    // Handle Reversed Transactions
    private static function handleReversedTransaction($payment)
    {
        $refund = $payment->refund()->where("status", "pending")->first();

        if ($refund) {
            $refund->update([
                "status" => "processed",
                "processed_at" => now(),
            ]);

            DB::commit();

            return ResponseHelpers::success(
                data: $payment->load("refund")->toArray()
            );
        }

        DB::commit();

        return ResponseHelpers::success(data: $refund->toArray());
    }
}
