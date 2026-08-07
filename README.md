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

- **`@rybbit` Blade directive** — injects the Rybbit tracking script with a single tag, with support for the `data-debounce`, `data-skip-patterns`, and `data-mask-patterns` script options.
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
],
```

```html
<script
    src="/rybbit/script.js"
    data-site-id="your-site-id"
    data-debounce="500"
    data-skip-patterns='["/admin/**"]'
    data-mask-patterns='["/users/**/profile"]'
    defer
></script>
```

### The tunnel

Browser tracking scripts and requests are frequently blocked by ad blockers and browser privacy features. To work around this, the package can register routes on your own domain that transparently proxy requests through to Rybbit:

| Method | Route (relative to `RYBBIT_TUNNEL_URL`, default `/rybbit`) | Forwards to    |
| ------ | ------------------------------------------------------------ | -------------- |
| GET    | `/script.js`, `/script-full.js`, `/replay.js`, `/metrics.js`  | `api/{script}` (cached) |
| POST   | `/track`                                                      | `api/track`    |
| POST   | `/identify`                                                   | `api/identify` |
| POST   | `/session-replay/record/{siteId}`                             | `api/session-replay/record/{siteId}` (queued) |
| GET    | `/site/tracking-config/{siteId}`                              | `api/site/tracking-config/{siteId}` (cached) |

The tunnel is enabled by default. Disable it if you'd rather send requests straight to Rybbit:

```env
RYBBIT_TUNNEL_ENABLED=false
```

You can customize the local path prefix, cache key prefix, and route middleware via `config/rybbit.php`.

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
