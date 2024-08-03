<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot()
    {
        $this->configureRateLimiting();

        // Define your routes
        $this->mapWebRoutes();
    }

    protected function configureRateLimiting()
    {
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('assets', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }

    protected function mapWebRoutes()
    {
        Route::middleware(['web', 'throttle:web'])
            ->group(base_path('routes/web.php'));

        Route::middleware(['web', 'throttle:assets'])
            ->prefix('storage')
            ->group(function () {
                Route::get('{file}', 'StorageController@show');
            });

        Route::middleware(['web', 'throttle:assets'])
            ->prefix('asset')
            ->group(function () {
                Route::get('{file}', 'AssetController@show');
            });
    }
}
