<?php

declare(strict_types=1);

use Cocosport\Rybbit\ForwardTunnelData;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

it('forwards the request with the given method, data, and headers', function () {
    Http::fake(['https://rybbit.io/api/track' => Http::response(['success' => true])]);

    (new ForwardTunnelData('https://rybbit.io/api/track', 'POST', ['event' => 'pageview'], ['X-Real-IP' => '1.2.3.4']))->handle();

    Http::assertSent(fn ($request) => $request->url() === 'https://rybbit.io/api/track'
        && $request->method() === 'POST'
        && $request['event'] === 'pageview'
        && $request->hasHeader('X-Real-IP', '1.2.3.4'));
});

it('does not throw when the upstream request fails and throw_on_error is disabled', function () {
    Sleep::fake();
    config()->set('rybbit.throw_on_error', false);
    Http::fake(['https://rybbit.io/api/track' => Http::response('upstream error', 500)]);

    (new ForwardTunnelData('https://rybbit.io/api/track', 'POST', [], []))->handle();
})->throwsNoExceptions();

it('throws when the upstream request fails and throw_on_error is enabled', function () {
    Sleep::fake();
    config()->set('rybbit.throw_on_error', true);
    Http::fake(['https://rybbit.io/api/track' => Http::response('upstream error', 500)]);

    (new ForwardTunnelData('https://rybbit.io/api/track', 'POST', [], []))->handle();
})->throws(RequestException::class);
