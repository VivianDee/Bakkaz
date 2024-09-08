<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->routes(function () {
            Route::prefix('admin/v1')
                 ->middleware('api')
                 ->namespace($this->namespace)
                 ->group(base_path('routes/admin/v1/api.php'));

            Route::prefix('app/v1')
                 ->middleware('api')
                 ->namespace($this->namespace)
                 ->group(base_path('routes/app/v1/api.php'));
        });
    }
}
