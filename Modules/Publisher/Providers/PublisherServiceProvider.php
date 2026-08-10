<?php

namespace Modules\Publisher\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class PublisherServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'publisher');
        $this->registerRoutes();
    }

    public function register(): void
    {
        //
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->namespace('Modules\Publisher\Http\Controllers\Frontend')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['web', 'auth'])
            ->namespace('Modules\Publisher\Http\Controllers\Admin')
            ->prefix('admin/publishers')
            ->name('admin.publishers.')
            ->group(__DIR__ . '/../Routes/admin.php');
    }
}
