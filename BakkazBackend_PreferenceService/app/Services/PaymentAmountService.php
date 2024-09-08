<?php

namespace App\Services;

use App\Helpers\ResponseHelpers;
use App\Models\PaymentAmount;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class PaymentAmountService
{
    static public function createPaymentAmount(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'type' => 'required|string',
                'amount' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();

            $payment_amount = PaymentAmount::create([
                'type' => $data['type'],
                'amount' => $data['amount'],
            ]);

            if (!$payment_amount) {
                // Return error response if Payment Amount does not exist
                return ResponseHelpers::error();
            }

            return ResponseHelpers::success(
                message: 'Amount added successfully'
            );
        } catch (ValidationException $e) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'type', 'amount'
                ])
            );
        } catch (\Throwable $th) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th
            );
        }
    }

    static public function showPaymentAmounts(Request $request)
    {
        $payment_amount = PaymentAmount::all();

        if (!$payment_amount) {
            // Return error response if Payment Amount does not exist
            return ResponseHelpers::notFound();
        }

        return ResponseHelpers::success(
            data: $payment_amount->toArray()
        );
    }

    static public function updatePaymentAmount(Request $request)
    {
        try {
            $validator =  Validator::make($request->all(), [
                'type' => 'required|string',
                'amount' => 'required|integer',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();

            $payment_amount = PaymentAmount::where('type', $data['type']);
            
            if (!$payment_amount) {
                // Return error response if Payment Amount does not exist
                return ResponseHelpers::notFound();
            }

            $payment_amount->update([
                'amount' => $data['amount'],
            ]);

            return ResponseHelpers::success(
                message: 'Amount updated successfully'
            );
        } catch (ValidationException $e) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'type', 'amount'
                ])
            );
        } catch (\Throwable $th) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th
            );
        }
    }
}