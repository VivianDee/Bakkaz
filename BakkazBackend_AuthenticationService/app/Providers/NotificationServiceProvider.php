<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Channels\FirebaseChannel;
use App\Services\PushNotificationService;
use GuzzleHttp\Client;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->when(FirebaseChannel::class)
                  ->needs(PushNotificationService::class)
                  ->give(function () {
                      return new PushNotificationService();
                  });
    }
}
