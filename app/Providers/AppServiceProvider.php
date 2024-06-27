<?php

namespace App\Providers;

use App\Contracts\ExternalAuthServiceInterface;
use App\Jobs\SendNotificationJob;
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
        $this->app->bindMethod([SendNotificationJob::class, 'handle'], function (SendNotificationJob $job) {
            return $job->handle();
        });
        
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
