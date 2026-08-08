<?php

declare(strict_types=1);

use Cocosport\Rybbit\Client;
use Cocosport\Rybbit\Exceptions\InvalidConfigurationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

it('does not throw when the configuration is valid', function () {
    config()->set('rybbit.host', 'https://app.rybbit.io');
    config()->set('rybbit.api_key', 'test-api-key');

    app(Client::class);
})->throwsNoExceptions();

it('throws when the host is missing', function () {
    config()->set('rybbit.host', null);

    app(Client::class);
})->throws(InvalidConfigurationException::class, 'The "rybbit.host" configuration value is missing. Set RYBBIT_HOST in your .env file.');

it('throws when the host is not a valid url', function () {
    config()->set('rybbit.host', 'not-a-url');

    app(Client::class);
})->throws(InvalidConfigurationException::class, 'The "rybbit.host" configuration value [not-a-url] is not a valid URL.');

it('throws when the site_seq_id is missing', function () {
    config()->set('rybbit.host', 'https://app.rybbit.io');
    config()->set('rybbit.site_seq_id', null);

    app(Client::class);
})->throws(InvalidConfigurationException::class, 'The "rybbit.site_seq_id" configuration value is missing. Set RYBBIT_SITE_SEQ_ID in your .env file.');

it('builds a site-scoped path', function () {
    config()->set('rybbit.host', 'https://app.rybbit.io');
    config()->set('rybbit.site_seq_id', '42');

    expect(app(Client::class)->sitePath('users'))->toBe('api/sites/42/users');
});

it('logs instead of throwing when misconfigured in production', function () {
    $this->app['env'] = 'production';
    config()->set('rybbit.host', null);
    Log::spy();

    app(Client::class);

    Log::shouldHaveReceived('error')->once();
});

it('does not log when misconfigured in production but logging is disabled', function () {
    $this->app['env'] = 'production';
    config()->set('rybbit.host', null);
    config()->set('rybbit.logs', false);
    Log::spy();

    app(Client::class);

    Log::shouldNotHaveReceived('error');
});

it('does not send requests when misconfigured in production', function () {
    $this->app['env'] = 'production';
    config()->set('rybbit.host', null);
    Http::fake();

    app(Client::class)->post('api/track', ['type' => 'pageview']);

    Http::assertNothingSent();
});

it('warns when the api key is missing', function () {
    config()->set('rybbit.host', 'https://app.rybbit.io');
    config()->set('rybbit.api_key', null);
    Log::spy();

    app(Client::class);

    Log::shouldHaveReceived('warning')->once();
});

it('does not warn when the api key is set', function () {
    config()->set('rybbit.host', 'https://app.rybbit.io');
    config()->set('rybbit.api_key', 'test-api-key');
    Log::spy();

    app(Client::class);

    Log::shouldNotHaveReceived('warning');
});

it('does not warn about the missing api key when logging is disabled', function () {
    config()->set('rybbit.host', 'https://app.rybbit.io');
    config()->set('rybbit.api_key', null);
    config()->set('rybbit.logs', false);
    Log::spy();

    app(Client::class);

    Log::shouldNotHaveReceived('warning');
});
