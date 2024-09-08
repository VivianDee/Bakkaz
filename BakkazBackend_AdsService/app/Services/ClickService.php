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
use App\Interfaces\ClickInterface;

class ClickService implements ClickInterface
{
    /// Clicks

    static public function handleClick(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'advertisement_id' => 'required|integer',
            ]);

            $advertisement_id = $request->input('advertisement_id');

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $ad = Advertisement::where('id', $advertisement_id)->first();

            if (!$ad) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 400,
                    message: 'No Advertisement found'
                );
            }

            $ad->increment('clicks');

            $click = Click::create([
                'user_id' => $request->input('user_id'), /// The ID of the user who clicked the Ad
                'advertisement_id' => $advertisement_id,
            ]);

            return ResponseHelpers::sendResponse(
                message: 'Advertisement Clicked Successfully',
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id', 'advertisement_id'
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
}
