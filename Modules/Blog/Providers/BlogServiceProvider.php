<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class BlogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'blog');
        $this->registerRoutes();
    }

    public function register(): void
    {
        //
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->namespace('Modules\Blog\Http\Controllers\Frontend')
            ->group(__DIR__ . '/../Routes/web.php');

        Route::middleware(['web', 'auth'])
            ->namespace('Modules\Blog\Http\Controllers\Admin')
            ->prefix('admin/blog')
            ->name('admin.blog.')
            ->group(__DIR__ . '/../Routes/admin.php');
    }
}
