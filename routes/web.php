<?php

use Cocosport\Rybbit\Http\Controllers\RybbitTunnelController;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Support\Facades\Route;

if (config('rybbit.tunnel.enabled')) {
    Route::prefix(config('rybbit.tunnel.url'))
        ->middleware(config('rybbit.tunnel.middleware'))
        ->withoutMiddleware(ConvertEmptyStringsToNull::class)
        ->group(function () {
            Route::get('/{script}', [RybbitTunnelController::class, 'proxyScript'])
                ->where('script', '(script|script-full|replay|metrics)\.js');

            Route::post('/track', [RybbitTunnelController::class, 'proxyTrack']);
            Route::post('/identify', [RybbitTunnelController::class, 'proxyIdentify']);
            Route::post('/session-replay/record/{siteId}', [RybbitTunnelController::class, 'proxySessionReplay']);

            Route::get('/site/tracking-config/{siteId}', [RybbitTunnelController::class, 'proxySiteConfig']);
        });
}
