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

## Table of Contents

- [Features](#features)
- [Feature Coverage](#feature-coverage)
- [Installation](#installation)
  - [Publishing the Configuration File](#publishing-the-configuration-file)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Injecting the tracking script](#injecting-the-tracking-script)
  - [Sending events server-side](#sending-events-server-side)
  - [Querying users](#querying-users)
  - [Querying overview stats](#querying-overview-stats)
  - [The tunnel](#the-tunnel)
  - [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Credits](#credits)
- [License](#license)

## Features

- **`@rybbit` Blade directive** — injects the Rybbit tracking script with a single tag, with support for the `data-debounce`, `data-tag`, `data-skip-patterns`, `data-mask-patterns`, and session replay script options.
- **Server-side event tracking** — the `Rybbit` facade sends pageviews, custom events, performance metrics, outbound clicks, and errors straight to Rybbit's `/api/track` endpoint from your backend.
- **User queries and deletion** — look up a site's users, a specific user's profile and devices, and their daily session counts through Rybbit's read API, or delete a user's tracked data.
- **Overview stats** — headline KPIs, bucketed time series, live visitor count, and dimension breakdowns (pages, countries, browsers, ...) for the configured site, e.g. for a daily-active-users chart.
- **First-party tunnel** — proxies tracking requests (script, `track`, `identify`, session replay, site config) through your own domain instead of Rybbit's, so the script isn't blocked by ad blockers or browser tracking protections.
- **Client IP forwarding** — the tunnel resolves the real visitor IP from `X-Forwarded-For` and forwards it to Rybbit, so proxied traffic is still attributed correctly.
- **Resilient forwarding** — failed tunnel requests are retried, optionally logged, and never cache a failed response; session replay data is forwarded through a queued job so it never blocks the request/response cycle.
- **`Rybbit::fake()`** — swap in an in-memory fake for your tests, with assertions for every tracking and user-query method and no real HTTP requests made.
- **Laravel Boost skill** — ships a `rybbit-development` [Agent Skill](https://laravel.com/docs/boost#agent-skills) that [Laravel Boost](https://laravel.com/docs/boost) auto-installs for your AI coding agent when you run `php artisan boost:install`, so it already knows how to use this package correctly.

## Feature Coverage

This SDK wraps the slice of [Rybbit](https://rybbit.com)'s platform our organization actually uses: embedding the tracking script, tunneling it through your own domain, sending events server-side, and reading back user data. Rybbit's [full API](https://rybbit.com/docs/api/getting-started) and dashboard cover a lot more ground that this package doesn't touch, notably:

- **Goals & Funnels** — conversion and drop-off analysis.
- **Sessions, Session Replay, Insights, Performance, Errors, and Bots queries** — analytics read endpoints beyond what `Rybbit::users()` and `Rybbit::overview()` expose (the tunnel forwards session replay *recording*, but nothing here queries it back).
- **Organizations, Teams, Sites, API Keys, and Data Import** — account and site administration endpoints.

We don't plan to cover all of Rybbit's features anytime soon, since this package tracks what we actually need. If you need one of the above (or anything else Rybbit offers), [open an issue](https://github.com/cocosport/rybbit-sdk-for-laravel/issues) describing your use case and we'll add it, or send a pull request — see [Contributing](#contributing).

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

By default the package points at Rybbit Cloud (`https://app.rybbit.io`). If you self-host Rybbit, point the SDK at your instance instead:

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
]);
```

`pathname`, `hostname`, `user_agent`, `ip_address`, and `querystring` default to the current request, and `user_id` defaults to the authenticated user's ID — so a call made from a controller or middleware doesn't need to repeat them. Pass any of them in `$data` to override, and they're dropped entirely when empty (e.g. `user_id` when nobody's authenticated, or when running outside an HTTP request).

Configure how `user_id` is resolved via `RYBBIT_USER_GUARD` (which auth guard to check) and `RYBBIT_USER_KEY` (an attribute or method to read off the resolved user, e.g. `uuid` or `publicKey`, instead of the default `getAuthIdentifier()`):

```env
RYBBIT_USER_GUARD=admin
RYBBIT_USER_KEY=publicKey
```

Every method returns the decoded JSON response as an `array` (e.g. `['success' => true]`), or `null` if the request couldn't reach Rybbit at all. Failed requests are logged (see `RYBBIT_LOGS`) and, when `RYBBIT_THROW_ON_ERROR` is enabled, raise a `RequestException` or `ConnectionException` instead of failing silently.

### Querying users

Use `Rybbit::users()` to read a site's [users](https://www.rybbit.io/docs/api/users/list) through Rybbit's API — this always requires `RYBBIT_API_KEY`.

```php
use Cocosport\Rybbit\Enums\SortOrder;
use Cocosport\Rybbit\Enums\UserSortBy;
use Cocosport\Rybbit\Facades\Rybbit;

Rybbit::users()->list(page: 1, pageSize: 25, sortBy: UserSortBy::Pageviews, sortOrder: SortOrder::Desc);

Rybbit::users()->sessionCount('abc123def456', ['time_zone' => 'America/New_York']);

Rybbit::users()->find('user@example.com');

Rybbit::users()->delete('user@example.com');
```

`list()` returns every user for the site, paginated; `sessionCount()` returns a user's daily session counts, keyed by date; `find()` returns one user's full profile — traits, locations, devices, and linked devices; `delete()` removes a user's tracked data from the site. Like the tracking methods above, each returns the decoded JSON response as an `array`, or `null` if the request couldn't reach Rybbit at all — see the linked docs for the exact payload shape.

### Querying overview stats

Use `Rybbit::overview()` to read the configured site's headline stats, time series, live visitor count, and dimension breakdowns through Rybbit's API — this always requires `RYBBIT_API_KEY`.

```php
use Cocosport\Rybbit\Enums\FilterParameter;
use Cocosport\Rybbit\Enums\TimeBucket;
use Cocosport\Rybbit\Facades\Rybbit;

Rybbit::overview()->summary(startDate: '2024-01-01', endDate: '2024-01-31');

Rybbit::overview()->timeSeries(TimeBucket::Day, startDate: '2024-01-01', endDate: '2024-01-31');

Rybbit::overview()->liveVisitors(minutes: 5);

Rybbit::overview()->metric(FilterParameter::Country, limit: 10);

Rybbit::overview()->pageTitles(limit: 10);
```

`summary()` returns headline KPIs (sessions, pageviews, unique users, bounce rate, ...) for the given range; `timeSeries()` returns the same KPIs bucketed over time — pass `bucket` as `TimeBucket::Day` for a daily-active-users chart (read the `users` field per bucket), or `TimeBucket::Hour`/`Week`/`Month`/`Year` for other granularities; `liveVisitors()` returns the number of unique visitors active right now; `metric()` breaks traffic down by a single `FilterParameter` dimension (`Pathname`, `Country`, `Browser`, `EventName`, ...); `pageTitles()` returns the site's most visited page titles. Omit the date range on `summary()`, `timeSeries()`, and `metric()` to query all time. Like the tracking and user methods above, each returns the decoded JSON response as an `array`, or `null` if the request couldn't reach Rybbit at all.

### The tunnel

Browser tracking scripts and requests are frequently blocked by ad blockers and browser privacy features. To work around this, the package registers routes under `RYBBIT_TUNNEL_URL` (default `/rybbit`) that transparently proxy the script, `track`, `identify`, session replay, and site config requests through to Rybbit.

It's enabled by default — point the `@rybbit` directive's `src` at your own domain instead of Rybbit's for free. Disable it if you'd rather talk to Rybbit directly:

```env
RYBBIT_TUNNEL_ENABLED=false
```

Customize the local path prefix, cache key prefix, and route middleware via `config/rybbit.php`.

### Testing

Call `Rybbit::fake()` in your tests to swap in an in-memory fake — no real HTTP requests are made, and every call is recorded for assertions:

```php
use Cocosport\Rybbit\Enums\FilterParameter;
use Cocosport\Rybbit\Facades\Rybbit;

Rybbit::fake();

// ... code under test calls Rybbit::track()->pageView(...) or Rybbit::users()->list(...) ...

Rybbit::assertPageViewSent(fn (array $data) => $data['pathname'] === '/checkout');
Rybbit::assertEventSent('purchase', fn (array $data) => $data['properties']['amount'] === 99.99);
Rybbit::assertOutboundSent('https://example.com');
Rybbit::assertErrorSent('TypeError');
Rybbit::assertNothingSent();
Rybbit::assertSentCount(2);

// escape hatch for anything not covered above
Rybbit::assertSent(fn (string $key, array $data) => $key === 'pageview');

// users() side
Rybbit::assertUsersListed(fn (array $query) => $query['sort_by'] === 'pageviews');
Rybbit::assertUserRequested('user@example.com');
Rybbit::assertUserDeleted('user@example.com');
Rybbit::assertSessionCountRequested('abc123def456');

// overview() side
Rybbit::assertOverviewRequested();
Rybbit::assertTimeSeriesRequested(fn (array $query) => $query['bucket'] === 'day');
Rybbit::assertLiveVisitorsRequested();
Rybbit::assertMetricRequested(FilterParameter::Country);
Rybbit::assertPageTitlesRequested();
```

Without a stub, faked calls return a sensible default (`['success' => true]` for tracking calls and `delete()`, an empty `data` payload for the other user and overview queries) so code under test that reads the response doesn't have to handle `null`. Pass stubs keyed by symbolic call name (`pageview`, `custom_event`, `performance`, `outbound`, `error`, `users.list`, `users.sessionCount`, `users.find`, `users.delete`, `overview.summary`, `overview.timeSeries`, `overview.liveVisitors`, `overview.metric`, `overview.pageTitles`) to control what's returned:

```php
Rybbit::fake([
    'users.find' => ['data' => ['user_id' => 'abc', 'identified_user_id' => 'user@example.com']],
]);
```

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
