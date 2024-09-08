<?php

namespace App\Services;

use App\Interfaces\SecurityInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Models\Preference;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class SecurityService implements SecurityInterface
{
    // Security
    static public function showSecurity(Request $request)
    {
        $user_id = $request->route('id');

        if ($user_id) {
            $preferences = Preference::where('user_id', $user_id)->with('security')->get();
        } else {
            $preferences = Preference::with('security')->get();
        }

        if ($preferences->isEmpty()) {
            return ResponseHelpers::notFound(message: "User Preference not found.");
        }

        $securities = $preferences->pluck('security')->filter();

        if ($securities->isEmpty()) {
            return ResponseHelpers::notFound(message: "Security Settings not found.");
        }

        return ResponseHelpers::sendResponse(data: $securities->toArray());
    }




    // Update Security 
    static public function updateSecuritySettings(Request $request)
    {
        try {
            // Validate the filtered data
            $validator =  Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'remember_me' => 'sometimes|boolean',
                'biometric_id' => 'sometimes|boolean',
                'face_id' => 'sometimes|boolean',
                'sms_authenticator' => 'sometimes|boolean',
                'google_authenticator' => 'sometimes|boolean',
            ]);


            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();


            // Retrieve preference and related models
            $preference = Preference::where('user_id', $request->input('user_id'))->first();

            if (!$preference) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: 'User Preferences Settings not found'
                );
            }
            
            unset($data['user_id']);

            // Filter out null values to only update provided fields
            $updateData = array_filter($data, function ($value) {
                return $value !== null;
            });

            // Update security settings
            $preference->security()->update($updateData);



            return ResponseHelpers::sendResponse(message: 'User Security Settings Updated Successfully');
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id',
                    "preference_id",
                    "remember_me",
                    "biometric_id",
                    "face_id",
                    "sms_authenticator",
                    "google_authenticator"
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }
}
