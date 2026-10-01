<?php

namespace Modules\SEO\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Blade;
use Modules\SEO\Services\AutoSeoScannerService;
use Modules\SEO\Services\SitemapService;

class SeoServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AutoSeoScannerService::class, fn () => new AutoSeoScannerService());
        $this->app->singleton(SitemapService::class, fn () => new SitemapService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'seo');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\SEO\Console\Commands\SeoScanCommand::class,
            ]);
        }

        $this->registerRoutes();
        $this->registerBladeDirectives();
    }

    /**
     * Register module routes.
     */
    protected function registerRoutes(): void
    {
        // Frontend & Public SEO Routes (Sitemap, Robots)
        Route::middleware('web')
            ->group(__DIR__ . '/../Routes/web.php');

        // Admin SEO Management Routes
        Route::middleware(['web', 'auth'])
            ->prefix('admin/seo')
            ->name('admin.seo.')
            ->group(__DIR__ . '/../Routes/admin.php');
    }

    /**
     * Register SEO Blade Directives
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('seoMetas', function ($expression) {
            return "<?php echo \Modules\SEO\Helpers\SeoHelper::render($expression); ?>";
        });
    }
}
