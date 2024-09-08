<?php

namespace App\Services;

use App\Enums\BakkazServiceType;
use Illuminate\Support\Facades\Mail;
use App\Helpers\ResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Mail\ContactFormMail;
use App\Models\ContactUs;
use App\Models\Subscriber;

class WebService
{
    static public function contactUs(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'message' => 'required|string',
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            if (!$request->header('service')) {
                return ResponseHelpers::error(message: "Service not found");
            }

            $service = BakkazServiceType::from($request->header('service', ''));

            $contact_us = ContactUs::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'message' => $request->input('message'),
                'service' => $service->value ?? null
            ]);

            
            if (!$contact_us) {
                return  ResponseHelpers::error(message: "Error sending message");
            }

            return ResponseHelpers::success(message: "Your message has been sent successfully!");
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: $e->getMessage()
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    static public function subscribe(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:subscribers,email|max:255',
            ]);

            throw_if($validator->fails(), new ValidationException($validator));

            if (!$request->header('service')) {
                return ResponseHelpers::error(message: "Service not found");
            }

            $service = BakkazServiceType::from($request->header('service', ''));
        
            $subscriber = Subscriber::create([
                'email' => $request->email,
                'service' => $service ?? null
            ]);

            if (!$subscriber) {
                return  ResponseHelpers::error(message: "Error Creating Subscriber");
            }

            return  ResponseHelpers::success(message: "You have been subscribed successfully!");
        } catch (ValidationException $e) {
            return ResponseHelpers::error(
                message: $e->getMessage()
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }
}
