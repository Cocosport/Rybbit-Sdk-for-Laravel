<?php

declare(strict_types=1);

use Cocosport\Rybbit\Rybbit;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config()->set('rybbit.host', 'https://rybbit.io');
    config()->set('rybbit.site_seq_id', '42');
    config()->set('rybbit.api_key', 'test-api-key');
});

it('sends page views to the track endpoint', function () {
    Http::fake(['https://rybbit.io/api/track' => Http::response(['success' => true])]);

    app(Rybbit::class)->track()->pageView(['pathname' => '/checkout']);

    Http::assertSent(fn ($request) => $request->url() === 'https://rybbit.io/api/track'
        && $request->method() === 'POST'
        && $request->hasHeader('Authorization', 'Bearer test-api-key')
        && $request['site_id'] === '42'
        && $request['type'] === 'pageview'
        && $request['pathname'] === '/checkout');
});

it('sends custom events with properties encoded as a json string', function () {
    Http::fake(['https://rybbit.io/api/track' => Http::response(['success' => true])]);

    app(Rybbit::class)->track()->event('purchase', ['amount' => 99.99, 'currency' => 'USD']);

    Http::assertSent(fn ($request) => $request['type'] === 'custom_event'
        && $request['event_name'] === 'purchase'
        && $request['properties'] === json_encode(['amount' => 99.99, 'currency' => 'USD']));
});

it('sends performance metrics as top level fields', function () {
    Http::fake(['https://rybbit.io/api/track' => Http::response(['success' => true])]);

    app(Rybbit::class)->track()->performance(['lcp' => 1200.5, 'cls' => 0.05]);

    Http::assertSent(fn ($request) => $request['type'] === 'performance'
        && $request['lcp'] === 1200.5
        && $request['cls'] === 0.05);
});

it('sends outbound link clicks with the url in properties', function () {
    Http::fake(['https://rybbit.io/api/track' => Http::response(['success' => true])]);

    app(Rybbit::class)->track()->outbound('https://example.com', ['text' => 'Example']);

    Http::assertSent(fn ($request) => $request['type'] === 'outbound'
        && $request['properties'] === json_encode(['url' => 'https://example.com', 'text' => 'Example']));
});

it('sends errors with the event name and message', function () {
    Http::fake(['https://rybbit.io/api/track' => Http::response(['success' => true])]);

    app(Rybbit::class)->track()->error('TypeError', 'Cannot read property of undefined', ['fileName' => 'app.js']);

    Http::assertSent(fn ($request) => $request['type'] === 'error'
        && $request['event_name'] === 'TypeError'
        && $request['properties'] === json_encode(['message' => 'Cannot read property of undefined', 'fileName' => 'app.js']));
});

it('omits the authorization header when no api key is configured', function () {
    config()->set('rybbit.api_key', null);
    Http::fake(['https://rybbit.io/api/track' => Http::response(['success' => true])]);

    app(Rybbit::class)->track()->pageView();

    Http::assertSent(fn ($request) => ! $request->hasHeader('Authorization'));
});

it('logs failed track requests', function () {
    Log::spy();
    Http::fake(['https://rybbit.io/api/track' => Http::response(['error' => 'nope'], 422)]);

    app(Rybbit::class)->track()->pageView();

    Log::shouldHaveReceived('warning')->once();
});

it('does not throw on failed track requests by default', function () {
    Http::fake(['https://rybbit.io/api/track' => Http::response(['error' => 'nope'], 422)]);

    app(Rybbit::class)->track()->pageView();
})->throwsNoExceptions();

it('throws on failed track requests when configured', function () {
    config()->set('rybbit.throw_on_error', true);
    Http::fake(['https://rybbit.io/api/track' => Http::response(['error' => 'nope'], 422)]);

    app(Rybbit::class)->track()->pageView();
})->throws(RequestException::class);

it('returns null on connection errors', function () {
    Http::fake(function () {
        throw new ConnectionException('Could not connect');
    });

    expect(app(Rybbit::class)->track()->pageView())->toBeNull();
});

it('throws on connection errors when configured', function () {
    config()->set('rybbit.throw_on_error', true);
    Http::fake(function () {
        throw new ConnectionException('Could not connect');
    });

    app(Rybbit::class)->track()->pageView();
})->throws(ConnectionException::class);
