<?php

namespace App\Services;

use App\Interfaces\AdvertisementInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Click;
use App\Models\Review;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

class AdvertisementService implements AdvertisementInterface
{


    /// Ads
    static public function showAd(Request $request)
    {

        $user_id = $request->route('id');

        if ($user_id) {
            $ads = Advertisement::where('user_id', $user_id)
                ->get();
        } else {

            $ads = Advertisement::get();
        }


        if ($ads->isEmpty()) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 404,
                message: 'No Advertisements found'
            );
        }


        return ResponseHelpers::sendResponse(data: $ads->toArray());
    }

    static public function createAd(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'price' => 'required|integer',
                'status' => 'required|string|in:Active,Expired',
                'clicks' => 'required|integer',
                'url' => 'required|string',
                'start_date' => 'required|date_format:Y-m-d H:i:s',
                'expiration_date' => 'required|date_format:Y-m-d H:i:s',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }



            $ad = Advertisement::create([
                'user_id' => $request->input('user_id'),
                'title' =>  $request->input('title'),
                'description' =>  $request->input('description'),
                'price' => $request->input('price'),
                'status' => $request->input('status'),
                'clicks' => $request->input('clicks'),
                'url' => $request->input('url'),
                'start_date' => $request->input('start_date'),
                'expiration_date' => $request->input('expiration_date'),

            ]);


            if (empty($ad)) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 400,
                    message: 'Failed To Create Advertisement'
                );
            }


            return ResponseHelpers::sendResponse(
                message: 'Advertisement Created Successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id', 'title', 'description', 'price',
                    'status', 'clicks', 'url',
                    'start_date', 'expiration_date'
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal server error'
            );
        }
    }


    static public function updateAd(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|integer',
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:255',
                'price' => 'sometimes|integer',
                'status' => 'sometimes|string|in:Active,Expired',
                'clicks' => 'sometimes|integer',
                'url' => 'sometimes|string',
                'start_date' => 'sometimes|date_format:Y-m-d H:i:s',
                'expiration_date' => 'sometimes|date_format:Y-m-d H:i:s',
            ]);


            $ad = Advertisement::where('user_id', $request->input('user_id'));

            $ad->update($data);

            return ResponseHelpers::sendResponse(
                message: 'Advertisement Updated Successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id', 'title', 'description', 'price',
                    'status', 'clicks', 'url',
                    'start_date', 'expiration_date'
                ])
            );
        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal server error'
            );
        }
    }

    static public function deleteAd(Request $request)
    {
        try {

            $ad_id = $request->route('ad_id');

            $ad = Advertisement::where('id', $ad_id)->first();

            if (!$ad) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 400,
                    message: 'No Advertisement found'
                );
            }

            $ad->delete();

            return ResponseHelpers::sendResponse(
                message: 'Advertisement Deleted Successfully'
            );


        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal Server Error'
            );
        }
    }

    static public function restoreAd(Request $request)
    {
        try {

            $ad_id  = $request->route('ad_id');


            $ad = Advertisement::withTrashed()->where('id', $ad_id)->first();

            if (!$ad) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 400,
                    message: 'No Advertisement found'
                );
            }

            $ad->restore();

            return ResponseHelpers::sendResponse(
                message: 'Advertisement Restored Successfully'
            );

        } catch (\Throwable $th) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 500,
                message: 'Internal Server Error'
            );
        }
    }
}
