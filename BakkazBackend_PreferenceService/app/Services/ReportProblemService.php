<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Models\Preference;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class ReportProblemService
{
    

    /// Report Problem

    static public function ReportProblem(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'message' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $user_id = $request->input('user_id');

            $preference = Preference::with('privacy')->where('user_id', $user_id)->first();

            if (!$preference || !$preference->privacy) {
                // Return error response if preference or privacy settings does not exist
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: !$preference ? 'User preference not found' : 'User privacy settings not found',
                );
            }

            $privacy = $preference->privacy()->where('preference_id', $preference->id)->first();
            
            
            $privacy->reportedProblems()->create([
                'message' =>  $request->input('message')
            ]);

            return ResponseHelpers::sendResponse(
                message: 'Problem reported successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id', 'message'
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

    static public function showReportedProblem(Request $request)
    {
        try {

            $user_id = $request->route('id');

            $preference = Preference::with('privacy')->where('user_id', $user_id)->first();

            if (!$preference || !$preference->privacy) {
                // Return error response if preference or privacy settings does not exist
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 404,
                    message: !$preference ? 'User preference not found' : 'User privacy settings not found',
                );
            }

            $privacy = $preference->privacy()->where('preference_id', $preference->id)->first();

            $reported_problem = $privacy->reportedProblems()->where('privacy_id', $privacy->id)->get();

            return ResponseHelpers::sendResponse(data: $reported_problem->toArray());
        } catch (\Throwable $th) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }
}