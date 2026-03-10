<?php

namespace App\Providers;

use App\Models\Visitor;
use App\Services\DocumentGeneratorService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DocumentGeneratorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        $exclude = ['dashboard', 'chart', 'storage', 'api', '.well-known'];

        if (
            request()->isMethod('get') &&
            !app()->runningInConsole() &&
            !collect($exclude)->contains(fn($prefix) => str_contains(request()->path(), $prefix)) &&
            !request()->has('_') &&
            !request()->has('page')
        ) {
            \DB::table('url_visitor')->insert([
                'id' => \Str::uuid(),
                'url' => request()->fullUrl(),
                'ip' => request()->ip(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (config('app.env') === 'production') {
     	   \URL::forceScheme('https');
    	}




         // define visitor data
         View::composer('layouts.user.footer', function ($view) {
            $visitor_by_day = Visitor::whereDate('created_at', Carbon::now()->format('Y-m-d'))->count();
            $visitor_by_month = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))->count();
            $visitor_by_year = Visitor::whereDate('created_at', '>=', Carbon::now()->startOfYear()->format('Y-m-d'))->count();
            $view->with(compact('visitor_by_day', 'visitor_by_month', 'visitor_by_year'));
        });
    }
}
