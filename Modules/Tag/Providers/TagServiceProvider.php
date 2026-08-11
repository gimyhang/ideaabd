<?php

declare(strict_types=1);

namespace Modules\Tag\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Tag\Services\TagService;

class TagServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(TagService::class, function ($app) {
            return new TagService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
    }
}
