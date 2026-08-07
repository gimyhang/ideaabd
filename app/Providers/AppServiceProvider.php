<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register global security headers middleware for web routes
        if ($this->app->runningInConsole() === false) {
            $router = $this->app['router'] ?? null;
            if ($router) {
                // push to web middleware group so it applies to standard web routes
                $router->pushMiddlewareToGroup('web', \App\Http\Middleware\SecurityHeaders::class);
            }
        }
    }
}
