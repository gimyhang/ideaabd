<?php

declare(strict_types=1);

namespace Modules\BulkOrder\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\BulkOrder\Services\BulkOrderService;

class BulkOrderServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(BulkOrderService::class, function ($app) {
            return new BulkOrderService();
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
