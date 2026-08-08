<?php

declare(strict_types=1);

use Cocosport\Rybbit\Exceptions\TunnelDisabledException;
use Cocosport\Rybbit\ForwardTunnelData;
use Cocosport\Rybbit\Http\Controllers\RybbitTunnelController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

it('resolves when the tunnel is enabled', function () {
    config()->set('rybbit.tunnel.enabled', true);

    expect(app(RybbitTunnelController::class))->toBeInstanceOf(RybbitTunnelController::class);
});

it('throws when the tunnel is disabled', function () {
    config()->set('rybbit.tunnel.enabled', false);

    app(RybbitTunnelController::class);
})->throws(TunnelDisabledException::class);

it('proxies and caches script requests', function () {
    Http::fake([
        'https://app.rybbit.io/api/script.js' => Http::response('console.log(1);', 200, [
            'Content-Type' => 'application/javascript',
        ]),
    ]);

    $this->get('/rybbit/script.js')->assertOk()->assertSee('console.log(1);', false);
    $this->get('/rybbit/script.js')->assertOk()->assertSee('console.log(1);', false);

    Http::assertSentCount(1);
    expect(Cache::has('rybbit_script_script.js'))->toBeTrue();
});

it('rejects script requests for unsupported script names', function () {
    $this->get('/rybbit/unknown.js')->assertNotFound();
});

it('does not cache failed script responses', function () {
    Http::fake([
        'https://app.rybbit.io/api/script.js' => Http::response('upstream error', 500),
    ]);

    $this->get('/rybbit/script.js')->assertStatus(500);

    expect(Cache::has('rybbit_script_script.js'))->toBeFalse();
});

it('proxies track requests', function () {
    Http::fake([
        'https://app.rybbit.io/api/track' => Http::response(['success' => true], 201),
    ]);

    $this->postJson('/rybbit/track', ['event' => 'pageview'])
        ->assertStatus(201)
        ->assertJson(['success' => true]);

    Http::assertSent(fn ($request) => $request->url() === 'https://app.rybbit.io/api/track'
        && $request->method() === 'POST'
        && $request['event'] === 'pageview');
});

it('proxies identify requests', function () {
    Http::fake([
        'https://app.rybbit.io/api/identify' => Http::response(['success' => true], 201),
    ]);

    $this->postJson('/rybbit/identify', ['userId' => 'user-1'])
        ->assertStatus(201)
        ->assertJson(['success' => true]);

    Http::assertSent(fn ($request) => $request->url() === 'https://app.rybbit.io/api/identify'
        && $request->method() === 'POST'
        && $request['userId'] === 'user-1');
});

it('queues session replay requests instead of forwarding them inline', function () {
    config()->set('rybbit.queue', 'rybbit-session-replay');
    Queue::fake();
    Http::fake();

    $this->postJson('/rybbit/session-replay/record/site-123', ['chunk' => 'binary-data'])
        ->assertOk()
        ->assertJson(['success' => true]);

    Queue::assertPushedOn('rybbit-session-replay', ForwardTunnelData::class);
    Http::assertNothingSent();
});

it('proxies and caches site config requests', function () {
    Http::fake([
        'https://app.rybbit.io/api/site/tracking-config/site-123' => Http::response(['trackErrors' => true], 200),
    ]);

    $this->get('/rybbit/site/tracking-config/site-123')->assertOk()->assertJson(['trackErrors' => true]);
    $this->get('/rybbit/site/tracking-config/site-123')->assertOk()->assertJson(['trackErrors' => true]);

    Http::assertSentCount(1);
    expect(Cache::has('rybbit_config_site-123'))->toBeTrue();
});
