<div align="center">
    <h1>Rybbit Sdk For Laravel</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/cocosport/rybbit-sdk-for-laravel"><img src="https://img.shields.io/packagist/v/cocosport/rybbit-sdk-for-laravel.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/cocosport/rybbit-sdk-for-laravel"><img src="https://img.shields.io/packagist/php-v/cocosport/rybbit-sdk-for-laravel.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/cocosport/rybbit-sdk-for-laravel"><img src="https://badge.laravel.cloud/badge/cocosport/rybbit-sdk-for-laravel?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/cocosport/rybbit-sdk-for-laravel/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/cocosport/rybbit-sdk-for-laravel/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/cocosport/rybbit-sdk-for-laravel"><img src="https://img.shields.io/packagist/dt/cocosport/rybbit-sdk-for-laravel.svg?style=flat-square" alt="Total Downloads"></a>
</p>

A simple, elegant Laravel package for integrating [Rybbit](https://www.rybbit.io) analytics into your application. This SDK provides a clean, Laravel-native way to add Rybbit's privacy-friendly, open-source analytics to your app.

## Features

- **`@rybbit` Blade directive** — injects the Rybbit tracking script with a single tag, with support for the `data-debounce`, `data-tag`, `data-skip-patterns`, `data-mask-patterns`, and session replay script options.
- **Server-side event tracking** — the `Rybbit` facade sends pageviews, custom events, performance metrics, outbound clicks, and errors straight to Rybbit's `/api/track` endpoint from your backend.
- **User queries** — look up a site's users, a specific user's profile and devices, and their daily session counts through Rybbit's read API.
- **First-party tunnel** — proxies tracking requests (script, `track`, `identify`, session replay, site config) through your own domain instead of Rybbit's, so the script isn't blocked by ad blockers or browser tracking protections.
- **Client IP forwarding** — the tunnel resolves the real visitor IP from `X-Forwarded-For` and forwards it to Rybbit, so proxied traffic is still attributed correctly.
- **Resilient forwarding** — failed tunnel requests are retried, optionally logged, and never cache a failed response; session replay data is forwarded through a queued job so it never blocks the request/response cycle.

## Installation

You can install the package via Composer:

```bash
composer require cocosport/rybbit-sdk-for-laravel
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="rybbit"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="rybbit-config"
```

## Configuration

At a minimum, set your site ID:

```env
RYBBIT_SITE_ID=your-site-id
```

By default the package points at Rybbit Cloud (`https://rybbit.io`). If you self-host Rybbit, point the SDK at your instance instead:

```env
RYBBIT_HOST=https://analytics.your-domain.com
```

See the published `config/rybbit.php` for every available option (script attributes, tunnel behavior, logging, queueing, ...) — each one is documented inline.

## Usage

### Injecting the tracking script

Add the `@rybbit` Blade directive to your layout, typically right before `</head>` or `</body>`:

```blade
<head>
    ...
    @rybbit
</head>
```

It renders nothing unless `RYBBIT_SITE_ID` is set. Otherwise, it outputs:

```html
<script
    src="/rybbit/script.js"
    data-site-id="your-site-id"
    defer
></script>
```

The `src` points at your own tunnel (see below) when the tunnel is enabled, or straight at your configured `RYBBIT_HOST` when it's disabled.

Set any of the [script config options](config/rybbit.php) to add the matching `data-*` attribute:

```env
RYBBIT_SCRIPT_DEBOUNCE=500
```

```php
// config/rybbit.php
'script' => [
    'debounce' => env('RYBBIT_SCRIPT_DEBOUNCE'),
    'skip_patterns' => ['/admin/**'],
    'mask_patterns' => ['/users/**/profile'],
    'tag' => env('RYBBIT_SCRIPT_TAG'),
    // ...
],
```

```html
<script
    src="/rybbit/script.js"
    data-site-id="your-site-id"
    data-debounce="500"
    data-tag="v2-launch"
    data-skip-patterns='["/admin/**"]'
    data-mask-patterns='["/users/**/profile"]'
    defer
></script>
```

Session replay attributes (`data-replay-*`) live under `script.replay` in the same config file, and only apply when session replay is enabled in your Rybbit site settings.

See the full [tracking script documentation](https://rybbit.com/docs/script) for every attribute and what it does.

### Sending events server-side

Use the `Rybbit` facade to send tracking events straight to Rybbit's [`/api/track`](https://www.rybbit.io/docs/api/sending-events) endpoint from your backend — useful for events that don't happen in the browser, like webhook-driven purchases or background jobs. Set `RYBBIT_SITE_SEQ_ID` (your site's internal numeric ID, shown in the Rybbit dashboard) and, for authenticated tracking that bypasses bot detection, `RYBBIT_API_KEY`.

```php
use Cocosport\Rybbit\Facades\Rybbit;

Rybbit::track()->pageView(['pathname' => '/checkout']);

Rybbit::track()->event('purchase', ['amount' => 99.99, 'currency' => 'USD']);

Rybbit::track()->performance(['lcp' => 1200.5, 'cls' => 0.05]);

Rybbit::track()->outbound('https://example.com', ['text' => 'Example link']);

Rybbit::track()->error('TypeError', 'Cannot read property of undefined', ['fileName' => 'app.js']);
```

Each method accepts an optional trailing `array $data` of additional top-level fields from the [request body](https://www.rybbit.io/docs/api/sending-events#request-body) (`user_id`, `hostname`, `referrer`, ...). For full control over the payload, or to send a type not covered by a dedicated method, call `send()` directly:

```php
Rybbit::track()->send('pageview', [
    'pathname' => '/checkout',
    'user_id' => $user->id,
]);
```

Every method returns the decoded JSON response as an `array` (e.g. `['success' => true]`), or `null` if the request couldn't reach Rybbit at all. Failed requests are logged (see `RYBBIT_LOGS`) and, when `RYBBIT_THROW_ON_ERROR` is enabled, raise a `RequestException` or `ConnectionException` instead of failing silently.

### Querying users

Use `Rybbit::users()` to read a site's [users](https://www.rybbit.io/docs/api/users/list) through Rybbit's API — this always requires `RYBBIT_API_KEY`.

```php
use Cocosport\Rybbit\Facades\Rybbit;

Rybbit::users()->list(['page' => 1, 'page_size' => 25, 'sort_by' => 'pageviews', 'sort_order' => 'desc']);

Rybbit::users()->sessionCount('abc123def456', ['time_zone' => 'America/New_York']);

Rybbit::users()->find('user@example.com');
```

`list()` returns every user for the site, paginated; `sessionCount()` returns a user's daily session counts, keyed by date; `find()` returns one user's full profile — traits, locations, devices, and linked devices. Like the tracking methods above, each returns the decoded JSON response as an `array`, or `null` if the request couldn't reach Rybbit at all — see the linked docs for the exact payload shape.

### The tunnel

Browser tracking scripts and requests are frequently blocked by ad blockers and browser privacy features. To work around this, the package registers routes under `RYBBIT_TUNNEL_URL` (default `/rybbit`) that transparently proxy the script, `track`, `identify`, session replay, and site config requests through to Rybbit.

It's enabled by default — point the `@rybbit` directive's `src` at your own domain instead of Rybbit's for free. Disable it if you'd rather talk to Rybbit directly:

```env
RYBBIT_TUNNEL_ENABLED=false
```

Customize the local path prefix, cache key prefix, and route middleware via `config/rybbit.php`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Rybbit Sdk For Laravel! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Carlo Eusebi](https://github.com/cocosport)
- [All Contributors](../../contributors)

## License

Rybbit Sdk For Laravel is open-sourced software licensed under the [MIT license](LICENSE.md).
