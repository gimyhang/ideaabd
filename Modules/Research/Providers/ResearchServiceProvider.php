<?php

namespace Modules\Research\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ResearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'research');
        $this->registerRoutes();
    }

    public function register(): void
    {
        //
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->namespace('Modules\Research\Http\Controllers\Frontend')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['web', 'auth'])
            ->namespace('Modules\Research\Http\Controllers\Admin')
            ->prefix('admin/research')
            ->name('admin.research.')
            ->group(__DIR__ . '/../Routes/admin.php');
    }
}
