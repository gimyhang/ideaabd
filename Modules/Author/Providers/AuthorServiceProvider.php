<?php

namespace Modules\Author\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AuthorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'author');
        $this->registerRoutes();
    }

    public function register(): void
    {
        //
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->namespace('Modules\Author\Http\Controllers\Frontend')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['web', 'auth'])
            ->namespace('Modules\Author\Http\Controllers\Admin')
            ->prefix('admin/authors')
            ->name('admin.authors.')
            ->group(__DIR__ . '/../Routes/admin.php');
    }
}
