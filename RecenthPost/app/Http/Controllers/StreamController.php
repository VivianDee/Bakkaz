<?php

namespace App\Http\Controllers;

use App\Service\StreamService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StreamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return StreamService::index($request);
    }

    public function startStream(Request $request): JsonResponse
    {
        return StreamService::startStream($request);
    }

    public function stopStream(Request $request): JsonResponse
    {
        return StreamService::stopStream($request);
    }

    public function getStreamsByUserId(Request $request): JsonResponse
    {
        return StreamService::getStreamsByUserId($request);
    }

    public function getStreamByStream(Request $request): JsonResponse
    {
        return StreamService::getStreamByStream($request);
    }


}
