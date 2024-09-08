<?php

namespace App\Services;

use App\Interfaces\ReportPostsInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Models\Preference;
use App\Models\ReportedPost;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class ReportPostsService
{
    

    /// Report Posts

    static public function ReportPost(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'reported_post_id' => 'required|integer',
                'reason' => 'required|string',
                'description' => 'required|string',
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
            
            
            $privacy->reportedPosts()->create([
                'reported_post_id' => $request->input('reported_post_id'),
                'reason' =>  $request->input('reason'),
                'description' => $request->input('description'),
            ]);

            return ResponseHelpers::sendResponse(
                message: 'Post reported successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id', 'reported_post_id', 'reason', 'description'
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

    static public function showReportedPosts(Request $request)
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

            $reported_Posts = $privacy->reportedPosts()->where('privacy_id', $privacy->id)->get();

            return ResponseHelpers::sendResponse(data: $reported_Posts->toArray());
        } catch (\Throwable $th) {

            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: $th->getMessage()
            );
        }
    }
}