<?php

namespace App\Service;

use App\Helpers\ResponseHelpers;
use App\Models\Stream;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Impl\Services\AuthImpl;


class StreamService
{
    public static function index(Request $request): JsonResponse
    {
        // Get all streams with an optional filter for 'is_live' status
        $isLive = $request->query('is_live');

        if ($isLive !== null) {
            $streams = Stream::where('is_live', filter_var($isLive, FILTER_VALIDATE_BOOLEAN))->get();
        } else {
            $streams = Stream::all();
        }

        return ResponseHelpers::success(message: 'Streams retrieved', data: $streams);
    }

    public static function startStream(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required',
            'channel_name' => 'nullable',

        ]);

        // Get user ID from the request
        $userId = $request->user_id;

        $user =  AuthImpl::getUserDetails($userId);

        // Check if the user already has an active stream
        $existingStream = Stream::where('user_id', $userId)->where('is_live', true)->first();

        if ($existingStream) {
            return ResponseHelpers::error(message: 'You already have an active stream. Please stop it first before starting a new one.');
        }

        // Create a new stream
        $stream = Stream::create([
            'user_id' => $userId,
            'channel_name' => $user['name'],
            'is_live' => true
        ]);

        return ResponseHelpers::success(message: 'Stream started', data: $stream);
    }

    public static function stopStream(Request $request): JsonResponse
    {
        // Get user ID from the request
        $userId = $request->route("user_id");

        // Find the user's active stream
        $stream = Stream::where('user_id', $userId)->where('is_live', true)->first();

        if (!$stream) {
            return ResponseHelpers::notFound(message: 'No active stream found');
        }

        // Update the stream to be inactive
        $stream->update(['is_live' => false]);

        return ResponseHelpers::success(message: 'Stream stopped');
    }

    public static function getStreamsByUserId(Request $request): JsonResponse
    {
        // Get user ID from the request
        $userId = $request->route("user_id");

        // Fetch all streams by the user ID with an optional filter for 'is_live' status
        $isLive = $request->query('is_live');

        if ($isLive !== null) {
            $streams = Stream::where('user_id', $userId)->where('is_live', filter_var($isLive, FILTER_VALIDATE_BOOLEAN))->get();
        } else {
            $streams = Stream::where('user_id', $userId)->get();
        }

        if ($streams->isEmpty()) {
            return ResponseHelpers::notFound(message: 'No streams found for this user');
        }

        return ResponseHelpers::success(message: 'Streams retrieved', data: $streams);
    }

    public static function getStreamByStream(Request $request): JsonResponse
    {
        // Get stream ID from the route parameters
        $stream_id = $request->route("stream_id");

        // Optional filter for live status
        $isLive = $request->query('is_live');

        // Fetch the stream by stream ID, optionally filtering by 'is_live' status
        $streamQuery = Stream::where('id', $stream_id);

        if ($isLive !== null) {
            $streamQuery->where('is_live', filter_var($isLive, FILTER_VALIDATE_BOOLEAN));
        }

        $stream = $streamQuery->first();

        if (!$stream) {
            return ResponseHelpers::notFound(message: 'No stream found');
        }

        return ResponseHelpers::success(message: 'Stream retrieved', data: $stream);
    }


}
