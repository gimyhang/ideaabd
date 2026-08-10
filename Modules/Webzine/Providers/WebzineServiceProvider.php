<?php

namespace Modules\Webzine\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class WebzineServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'webzine');
        $this->registerRoutes();
    }

    public function register(): void
    {
        //
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->namespace('Modules\Webzine\Http\Controllers\Frontend')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['web', 'auth'])
            ->namespace('Modules\Webzine\Http\Controllers\Admin')
            ->prefix('admin/webzines')
            ->name('admin.webzines.')
            ->group(__DIR__ . '/../Routes/admin.php');
    }
}
