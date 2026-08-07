<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class ModularServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $modulesPath = base_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {
            $moduleName = basename($module);

            // ১. রাউটস অটো-লোড (web.php)
            $webRoute = "{$module}/Routes/web.php";
            if (File::exists($webRoute)) {
                Route::middleware('web')->group($webRoute);
            }

            // ২. API রাউটস অটো-লোড (api.php)
            $apiRoute = "{$module}/Routes/api.php";
            if (File::exists($apiRoute)) {
                Route::middleware('api')->prefix('api')->group($apiRoute);
            }

            // ৩. ভিউ অটো-লোড (যেমন: view('vendor::dashboard'))
            $viewsPath = "{$module}/Resources/views";
            if (File::exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, strtolower($moduleName));
            }

            // ৪. মাইগ্রেশন অটো-লোড
            $migrationsPath = "{$module}/Database/Migrations";
            if (File::exists($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }
}