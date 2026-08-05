<?php

declare(strict_types=1);

namespace Rybbit\Rybbit;

use Illuminate\Support\ServiceProvider;

class RybbitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rybbit-sdk-for-laravel.php', 'rybbit-sdk-for-laravel');

        $this->app->singleton(Rybbit::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/rybbit-sdk-for-laravel.php' => config_path('rybbit-sdk-for-laravel.php'),
        ], ['rybbit-sdk-for-laravel', 'rybbit-sdk-for-laravel-config']);
    }
}
