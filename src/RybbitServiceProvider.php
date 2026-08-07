<?php

declare(strict_types=1);

namespace Cocosport\Rybbit;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class RybbitServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rybbit.php', 'rybbit');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->registerBladeDirectives();
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
            __DIR__.'/../config/rybbit.php' => config_path('rybbit.php'),
        ], ['rybbit', 'rybbit-config']);
    }

    protected function registerBladeDirectives(): void
    {
        $this->callAfterResolving('blade.compiler', function (BladeCompiler $blade) {
            $blade->directive('rybbit', [Directive::class, 'injectedScript']);
        });
    }
}
