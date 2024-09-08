<?php

namespace App\Services;

use App\Impl\LiveImpl;

class LiveStreamService extends LiveImpl
{
    static public function createLiveStream($streamName, $inputType = 'rtmp', $idleTimeout = 120, $maxRuntime = 43200)
    {
        return parent::createLiveStream($streamName, $inputType, $idleTimeout, $maxRuntime);
    }

    static public function activateLiveStream($streamId)
    {
        return parent::activateLiveStream($streamId);
    }

    static public function setLiveStreamToIdle($streamId)
    {
        return parent::setLiveStreamToIdle($streamId);
    }

    static public function deleteLiveStream($streamId)
    {
        return parent::deleteLiveStream($streamId);
    }

    static public function createLiveStreamOutput($streamId, $name, $type, $uri, $streamKey, $vendor)
    {
        return parent::createLiveStreamOutput($streamId, $name, $type, $uri, $streamKey, $vendor);
    }
}
