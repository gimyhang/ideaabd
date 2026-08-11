<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
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

        // Sidebar/topbar badge — resolved once per admin request, not per partial.
        View::composer(['admin.partials.sidebar', 'admin.partials.topbar'], function ($view) {
            $view->with('adminPendingRegistrations', once(
                fn () => app(\App\Services\AdminDashboardService::class)->pendingRegistrations()
            ));
        });

        // Bengali number/date directives used across the admin panel.
        Blade::directive('bn',    fn ($e) => "<?php echo \App\Support\Bn::num($e); ?>");
        Blade::directive('taka',  fn ($e) => "<?php echo \App\Support\Bn::money($e); ?>");
        Blade::directive('takaS', fn ($e) => "<?php echo \App\Support\Bn::moneyShort($e); ?>");
        Blade::directive('bnDate', fn ($e) => "<?php echo \App\Support\Bn::date($e); ?>");
    }
}
