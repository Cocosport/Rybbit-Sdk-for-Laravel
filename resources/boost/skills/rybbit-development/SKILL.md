---
name: rybbit-development
description: Build and work with Rybbit analytics features in this Laravel app via cocosport/rybbit-sdk-for-laravel, including the tracking script directive, server-side event tracking, user queries, and testing with Rybbit::fake(). Use this skill whenever the user asks to add analytics tracking, inject the Rybbit script, track a pageview/event/error/outbound click from the backend, look up Rybbit users, or write tests involving Rybbit.
---

# Rybbit Development

## When to use this skill

Use this skill when working with `cocosport/rybbit-sdk-for-laravel` features: injecting the tracking script, sending events from server-side code (jobs, webhooks, controllers), querying users through Rybbit's API, or testing any of the above.

## Setup

At minimum, set `RYBBIT_SITE_ID` (the public site ID shown in the Rybbit dashboard) to enable the tracking script. For server-side calls (`Rybbit::track()`, `Rybbit::users()`), also set `RYBBIT_SITE_SEQ_ID` (the site's internal numeric ID) and, ideally, `RYBBIT_API_KEY` (bearer token — optional but recommended, since it bypasses bot detection). If self-hosting Rybbit, set `RYBBIT_HOST`. See `config/rybbit.php` (publish with `php artisan vendor:publish --tag=rybbit-config`) for every option, including script attributes, session replay, the tunnel, logging, and error-throwing behavior.

## Injecting the tracking script

Add the `@rybbit` Blade directive inside `<head>`, once per layout:

```blade
<head>
    @rybbit
</head>
```

It renders nothing unless `RYBBIT_SITE_ID` is set. Script attributes (`data-debounce`, `data-tag`, `data-skip-patterns`, `data-mask-patterns`, session replay attributes) come from `config/rybbit.php` — don't hand-write the `<script>` tag or pass attributes directly to the directive.

## Sending events server-side

Use the `Rybbit` facade for events that don't happen in the browser (webhook-driven purchases, background jobs, server-rendered flows):

```php
use Cocosport\Rybbit\Facades\Rybbit;

Rybbit::track()->pageView(['pathname' => '/checkout']);
Rybbit::track()->event('purchase', ['amount' => 99.99, 'currency' => 'USD']);
Rybbit::track()->performance(['lcp' => 1200.5, 'cls' => 0.05]);
Rybbit::track()->outbound('https://example.com', ['text' => 'Example link']);
Rybbit::track()->error('TypeError', 'Cannot read property of undefined');
```

Every method returns the decoded JSON response as an `array`, or `null` if the request couldn't reach Rybbit — never assume a `Response` object or that the result is non-null. For a type not covered by a dedicated method, call `Rybbit::track()->send($type, $data)` directly.

`pathname`, `hostname`, `user_agent`, `ip_address`, and `querystring` default to the current request, and `user_id` defaults to the authenticated user's ID — don't manually pass these unless overriding them. If the app needs a different auth guard or a non-default identifier (e.g. a UUID or a custom method like `publicKey()`), configure `RYBBIT_USER_GUARD` / `RYBBIT_USER_KEY` (or `rybbit.user.guard` / `rybbit.user.key`) instead of passing `user_id` on every call.

## Querying users

Use `Rybbit::users()` to read analytics data back out — this always requires `RYBBIT_API_KEY`:

```php
Rybbit::users()->list(['page' => 1, 'sort_by' => 'pageviews', 'sort_order' => 'desc']);
Rybbit::users()->sessionCount('abc123def456', ['time_zone' => 'America/New_York']);
Rybbit::users()->find('user@example.com');
```

Same return contract as the tracking methods: decoded `array`, or `null` on failure.

## Testing

Never let a test hit Rybbit's real API or mock the `Http` facade directly for this — call `Rybbit::fake()` instead and assert against it:

```php
use Cocosport\Rybbit\Facades\Rybbit;

Rybbit::fake();

// ... code under test ...

Rybbit::assertPageViewSent(fn (array $data) => $data['pathname'] === '/checkout');
Rybbit::assertEventSent('purchase', fn (array $data) => $data['properties']['amount'] === 99.99);
Rybbit::assertOutboundSent('https://example.com');
Rybbit::assertErrorSent('TypeError');
Rybbit::assertUsersListed();
Rybbit::assertUserRequested('user@example.com');
Rybbit::assertSessionCountRequested('abc123def456');
Rybbit::assertNothingSent();
Rybbit::assertSentCount(2);

// escape hatch for anything not covered above
Rybbit::assertSent(fn (string $key, array $data) => $key === 'pageview');
```

Without a stub, faked calls return a sensible default (`['success' => true]` for tracking, an empty `data` payload for user queries), so code under test that reads the response doesn't need extra null handling. Stub specific responses keyed by symbolic call name:

```php
Rybbit::fake([
    'users.find' => ['data' => ['user_id' => 'abc', 'identified_user_id' => 'user@example.com']],
]);
```
