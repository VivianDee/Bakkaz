<?php

namespace App\Http\Controllers;

use App\Services\LiveStreamService;
use Illuminate\Http\Request;

class LiveStreamController extends Controller
{
    public function createLiveStream(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'streamName' => 'required|string|max:255',
            'inputType' => 'required|string|in:rtmp,other', // Adjust 'other' to your acceptable types
            'idleTimeout' => 'required|integer|min:0',
            'maxRuntime' => 'required|integer|min:0'
        ]);

        // Pass validated data to the service
        return LiveStreamService::createLiveStream(
            $validatedData['streamName'],
            $validatedData['inputType'],
            $validatedData['idleTimeout'],
            $validatedData['maxRuntime']
        );
    }

    public function activateLiveStream(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'streamId' => 'required|string'
        ]);

        // Pass validated data to the service
        return LiveStreamService::activateLiveStream($validatedData['streamId']);
    }

    public function setLiveStreamToIdle(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'streamId' => 'required|string'
        ]);

        // Pass validated data to the service
        return LiveStreamService::setLiveStreamToIdle($validatedData['streamId']);
    }

    public function deleteLiveStream(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'streamId' => 'required|string'
        ]);

        // Pass validated data to the service
        return LiveStreamService::deleteLiveStream($validatedData['streamId']);
    }

    public function createLiveStreamOutput(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'uri' => 'required|url',
            'stream_key' => 'required|string',
            'vendor' => 'required|string'
        ]);

        // Pass validated data to the service
        return LiveStreamService::createLiveStreamOutput(
           $request->route('streamId'),
            $validatedData['name'],
            $validatedData['type'],
            $validatedData['uri'],
            $validatedData['stream_key'],
            $validatedData['vendor']
        );
    }
}
