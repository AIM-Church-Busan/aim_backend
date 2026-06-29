<?php

namespace App\Providers;

use App\Socialite\PlanningCenterProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Socialite::extend('planning-center', function ($app) {
            $config = $app['config']['services.planning-center'];

            return Socialite::buildProvider(PlanningCenterProvider::class, $config);
        });
    }
}
