<?php

namespace App\Services;

use App\Interfaces\ReviewInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelpers;
use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Click;
use App\Models\Review;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

class ReviewService implements ReviewInterface
{

    /// Review
    static public function reviewAd(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'advertisement_id' => 'required|integer',
                'rating' => 'required|integer',
                'comment' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $advertisement = Advertisement::with('review')->where('id', $request->input('advertisement_id'))->first();

            if (!$advertisement) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 400,
                    message: 'No Advertisement found'
                );
            }

            $advertisement->review()->create([
                'advertisement_id' => $request->input('advertisement_id'),
                'rating' => $request->input('rating'),
                'comment' => $request->input('comment'),
            ]);

            return ResponseHelpers::sendResponse(
                message: 'Review Created Successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'advertisement_id',
                    'rating', 'comment'
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


    static public function showReviews(Request $request)
    {
        $advertisement_id = $request->route('ad_id');

        if ($advertisement_id) {
            $reviews = Review::where('advertisement_id', $advertisement_id)
                ->get();
        } else {
            // $reviews = Review::withTrashed()->get();
            $reviews = Review::get();
        }


        if (!$reviews) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 404,
                message: 'No Reviews found'
            );
        }


        return ResponseHelpers::sendResponse(data: $reviews->toArray());
    }


    static public function updateReview(Request $request)
    {
        try {
            $data = $request->validate([
                'advertisement_id' => 'required|integer',
                'rating' => 'sometimes|integer',
                'comment' => 'sometimes|string|max:255',
            ]);


            $advertisement = Advertisement::with('review')->where('id', $request->input('advertisement_id'))->first();

            if (!$advertisement) {
                return ResponseHelpers::sendResponse(
                    status: false,
                    statusCode: 400,
                    message: 'No Advertisement found'
                );
            }

            $advertisement->review->update($data);

            return ResponseHelpers::sendResponse(
                message: 'Review Updated Successfully'
            );
        } catch (ValidationException $e) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: ResponseHelpers::implodeNestedArray($e->errors(), [
                    'user_id', 'advertisement_id',
                    'rating', 'comment'
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

    static public function deleteReview(Request $request)
    {

        $review_id = $request->route('review_id');

        $review = Review::where('id', $review_id);

        if (!$review) {
            return ResponseHelpers::sendResponse(
                status: false,
                statusCode: 400,
                message: 'No Review found'
            );
        }

        $review->delete();

        return ResponseHelpers::sendResponse(
            message: "Review Deleted Successfully"
        );
    }
}
