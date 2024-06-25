<?php

namespace App\Providers;

use App\Contracts\ExternalAuthServiceInterface;
use App\Services\ExternalAuthService;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ExternalAuthServiceInterface::class, ExternalAuthService::class);
        Sanctum::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
