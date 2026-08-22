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

        // Sidebar/topbar badge & notification alerts — resolved once per admin request.
        View::composer(['admin.partials.sidebar', 'admin.partials.topbar', 'admin.dashboard'], function ($view) {
            $dashboardService = app(\App\Services\AdminDashboardService::class);
            $view->with('adminPendingRegistrations', once(
                fn () => $dashboardService->pendingRegistrations()
            ));
            $view->with('adminPendingAlerts', once(
                fn () => $dashboardService->getPendingAlerts()
            ));
        });

        // Bengali number/date directives used across the admin panel.
        Blade::directive('bn',    fn ($e) => "<?php echo \App\Support\Bn::num($e); ?>");
        Blade::directive('taka',  fn ($e) => "<?php echo \App\Support\Bn::money($e); ?>");
        Blade::directive('takaS', fn ($e) => "<?php echo \App\Support\Bn::moneyShort($e); ?>");
        Blade::directive('takaInWords', fn ($e) => "<?php echo \App\Support\Bn::inWords($e); ?>");
        Blade::directive('bnDate', fn ($e) => "<?php echo \App\Support\Bn::date($e); ?>");

        // Set modern customized pagination across all views
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.custom');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.custom');
        // Auto-heal/verify blog_posts subtitle column if missing (cached check to avoid slowing down HTTP requests)
        if (!app()->runningInConsole()) {
            \Illuminate\Support\Facades\Cache::remember('db_schema_auto_healed_v2', 86400, function () {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('blog_posts') && !\Illuminate\Support\Facades\Schema::hasColumn('blog_posts', 'subtitle')) {
                        $driver = \Illuminate\Support\Facades\DB::getDriverName();
                        if ($driver === 'sqlite') {
                            \Illuminate\Support\Facades\DB::statement('ALTER TABLE blog_posts ADD COLUMN subtitle VARCHAR(500) NULL');
                        } else {
                            \Illuminate\Support\Facades\DB::statement('ALTER TABLE `blog_posts` ADD COLUMN `subtitle` VARCHAR(500) NULL AFTER `title`');
                        }
                    }

                    \App\Models\IdeaInvoice::ensureColumnsExist();
                } catch (\Throwable $e) {
                    // Ignore gracefully if schema modifications are restricted
                }
                return true;
            });
        }
    }
}
