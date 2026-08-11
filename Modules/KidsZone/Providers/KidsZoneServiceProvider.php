<?php

declare(strict_types=1);

namespace Modules\KidsZone\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\KidsZone\Services\KidsZoneService;

class KidsZoneServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(KidsZoneService::class, function ($app) {
            return new KidsZoneService();
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
