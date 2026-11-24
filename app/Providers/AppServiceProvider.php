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
        // Text saved on /admin/translations is laid over the lang/*.php files
        $this->app->extend('translation.loader', fn ($loader) => new \App\Support\DbTranslationLoader($loader));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Guest page cache (App\Http\Middleware\GuestPageCache): content that public pages show was saved or
        // deleted, so every cached page is dropped. Busy write-only models (shares, wishlists, logs) are left out.
        \Illuminate\Support\Facades\Event::listen(['eloquent.saved: *', 'eloquent.deleted: *'], function (string $event) {
            static $watched = [
                \Modules\Book\Models\Book::class, \Modules\Book\Models\Category::class, \Modules\Book\Models\Genre::class,
                \Modules\Author\Models\Author::class, \Modules\Blog\Models\BlogPost::class, \Modules\Blog\Models\BlogCategory::class,
                \Modules\Blog\Models\BlogTag::class, \Modules\Ebook\Models\Ebook::class, \Modules\Publisher\Models\Publisher::class,
                \Modules\Webzine\Models\Webzine::class, \Modules\Webzine\Models\WebzineArticle::class,
                \App\Models\AdminDashboardSetting::class, \App\Models\Product::class, \App\Models\ProductCategory::class,
            ];
            $class = trim(substr($event, strpos($event, ':') + 1));
            if (in_array($class, $watched, true)
                || in_array(class_basename($class), ['Review', 'Promotion', 'HotDeal', 'KidsZone', 'ResearchPaper', 'SeoMeta', 'Tag'], true)) {
                \App\Http\Middleware\GuestPageCache::flush();
            }
        });

        // Enforce HTTPS across all URLs, assets, forms and redirects on live domain
        if ($this->app->environment('production') || (isset($_SERVER['HTTP_HOST']) && str_contains($_SERVER['HTTP_HOST'], 'ideaabd.com'))) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // public/.htaccess sends every visitor (and Facebook/Google) to https://www.ideaabd.com. Generated links must
        // use that same address: links on "ideaabd.com" redirect to www, and a page whose og:url / canonical points
        // back to the non-www address is a redirect loop for Facebook's crawler (shared links stay plain text, no preview).
        if (isset($_SERVER['HTTP_HOST']) && preg_match('/(^|\.)ideaabd\.com(:\d+)?$/i', (string) $_SERVER['HTTP_HOST'])) {
            \Illuminate\Support\Facades\URL::forceRootUrl('https://www.ideaabd.com');
        }

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

        // Bengali/English number/date directives used across the admin panel.
        Blade::directive('bn',    fn ($e) => "<?php echo \App\Support\Bn::num($e); ?>");
        Blade::directive('taka',  fn ($e) => "<?php echo \App\Support\Bn::money($e); ?>");
        Blade::directive('takaS', fn ($e) => "<?php echo \App\Support\Bn::moneyShort($e); ?>");
        Blade::directive('takaInWords', fn ($e) => "<?php echo \App\Support\Bn::inWords($e); ?>");
        Blade::directive('takaInWordsEn', fn ($e) => "<?php echo \App\Support\Bn::inWordsEn($e); ?>");
        Blade::directive('bnDate', fn ($e) => "<?php echo \App\Support\Bn::date($e); ?>");

        // Set modern customized pagination across all views
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.custom');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.custom');

        // Apply dynamic SMTP settings for web requests
        if (!app()->runningInConsole()) {
            try {
                \App\Services\EmailService::applyRuntimeSmtpConfig();
            } catch (\Throwable $e) {
                // Ignore if DB not ready yet
            }
        }

        // Auto-heal/verify blog_posts subtitle column if missing (cached check to avoid slowing down HTTP requests)
        if (!app()->runningInConsole()) {
            \Illuminate\Support\Facades\Cache::remember('db_schema_auto_healed_v2', 86400, function () {
                try {
                    if (\App\Support\SchemaCache::has('blog_posts') && !\App\Support\SchemaCache::hasColumn('blog_posts', 'subtitle')) {
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
