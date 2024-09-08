<?php

namespace App\service;

use App\Helpers\DateHelper;
use App\Helpers\ResponseHelpers;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Impl\Services\AuthImpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PollService
{
    /// Create Poll Method
    static function createPost(Request $request)
    {
        try {
            $request->validate([
                "poll_question" => "required|string",
                "state" => "required|string",
                "city" => "required|string",
                "device" => "required|string",
                "post_type" => "required|string",
                "options" => "required|array",
                "duration" => "required",
                "user_id" => "required",
            ]);


            if (count($request->options) > 5) {
                return ResponseHelpers::unprocessableEntity(
                    message: "You can only add a maximum of 5 options"
                );
            }

            if (count($request->options) < 2) {
                return ResponseHelpers::unprocessableEntity(
                    message: "You can only add a minimum of 2 options"
                );
            }

            $date = DateHelper::getFutureDateTime($request->duration);

            DB::beginTransaction();

            $new_poll = Poll::create([
                "user_id" => $request->user_id,
                "poll_question" => $request->poll_question,
                "state" => $request->state,
                "city" => $request->city,
                "device" => $request->device,
                // "expiresAt" => $date,
            ]);

            foreach ($request->options as $value) {
                $options = PollOption::create([
                    "poll_id" => $new_poll->id,
                    "option_value" => $value,
                    "poll_option_votes" => 0,
                ]);
            }
            DB::commit();

            return ResponseHelpers::created(message: "Poll Posted");
        } catch (ValidationException $th) {
            return ResponseHelpers::error(
                ResponseHelpers::implodeNestedArray($th->errors(), [
                    "user_id",
                    "poll_question",
                    "state",
                    "city",
                    "device",
                    "post_type",
                    "options",
                ])
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelpers::internalServerError(
                message: $th->getMessage()
            );
        }
    }

    static function voteOnPoll(Request $request)
    {
        $validator = $request->validate([
            "poll_option_id" => ["required"],
            "user_id" => ["required",]
        ]);

        $user_id = $request->input('user_id');

        // Determine whether to react or unreact
        $voted = PollVote::where("user_id", $user_id)
            ->where("poll_option_id", $request->poll_option_id);

        if ($voted->exists()) {
            // Unreact the post
            PollVote::where("user_id", $user_id)
                ->where("poll_option_id", $request->poll_option_id)
                ->delete();

            // $option = PollOption::where(
            //     "id",
            //     $voted->first()->poll_option_id
            // );

            // $option->update([
            //     "poll_option_votes" => $option->poll_option_votes--,
            // ]);

            // var_dump($option->get());

            return ResponseHelpers::created("UnVoted");
        } else {

            $vote = PollVote::create([
                "user_id" => $user_id,
                "poll_option_id" => $request->poll_option_id,
            ]);

            if ($vote) {

                //     $option = PollOption::where("id", $vote->poll_option_id);

                //     $option->update([
                //         "poll_option_votes" => $option->poll_option_votes++,
                //     ]);
                //     var_dump($option->get());

                return ResponseHelpers::created("Voted");
            }
        }
        return ResponseHelpers::error("Unable to vote");
    }

    public function pollVotes(Request $request)
    {
        // $poll_id = $request->route('poll_id');

        return PollVote::with('option')->get();
    }
}
