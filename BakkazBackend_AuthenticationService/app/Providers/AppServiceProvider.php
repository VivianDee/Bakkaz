<?php

namespace App\Providers;

use App\Enums\AccountType;
use App\Impl\DigitalOceanCdnImpl;
use App\Interfaces\DigitalOceanCdnInterface;
use App\Models\Asset;
use App\Models\Message;
use App\Models\User;
use App\Observers\MessageObserver;
use App\Services\Services;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Pulse\Facades\Pulse;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Message::observe(MessageObserver::class);

        /// uncomment to auth users to view pulse dashboard
        Gate::define('viewPulse', function (User $user) {
            return $user->account_type ==  AccountType::AdminSignUp->name;
        });

        Pulse::user(fn ($user) => [
            'name' => $user->name,
            'extra' => $user->email,
            'avatar' => Asset::where('user_id', $user->id)->first()->path ?? '',
        ]);
    }
}
