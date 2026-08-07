# Release Notes

## [Unreleased](https://github.com/cocosport/rybbit-sdk-for-laravel/compare/v0.2.2...HEAD)

## [v0.2.2](https://github.com/cocosport/rybbit-sdk-for-laravel/compare/v0.2.1...v0.2.2) - 2026-08-07

<!-- Release notes generated using configuration in .github/release.yml at main -->
### What's Changed

#### Other Changes

* Replace Users::list() array $query with explicit named parameters by @carloeusebi in https://github.com/Cocosport/Rybbit-Sdk-for-Laravel/pull/1

### New Contributors

* @carloeusebi made their first contribution in https://github.com/Cocosport/Rybbit-Sdk-for-Laravel/pull/1

**Full Changelog**: https://github.com/Cocosport/Rybbit-Sdk-for-Laravel/compare/v0.2.1...v0.2.2

## [v0.2.1](https://github.com/cocosport/rybbit-sdk-for-laravel/compare/v0.2.0...v0.2.1) - 2026-08-07

### Bug Fixes

- Queued tunnel forwarding (`ForwardTunnelData`) now honors `rybbit.throw_on_error`. A failed upstream response no longer throws unconditionally via the HTTP client's own `retry()` default (`throw: true`), which bypassed the config check entirely. Now matches the synchronous `Tunnel::forward()` behavior, which already passed `throw: false`.
- `Rybbit::users()` now validates `rybbit.site_seq_id` the same way `rybbit.host` is validated (throws in non-production, logs and fails soft in production). Site-scoped path building (`api/sites/:site/...`) moved into `Client::sitePath()`, so future site-scoped resources get the same validation and path building for free.

**Full Changelog**: https://github.com/cocosport/rybbit-sdk-for-laravel/compare/v0.2.0...v0.2.1

## [v0.2.0](https://github.com/cocosport/rybbit-sdk-for-laravel/compare/v0.1.0...v0.2.0) - 2026-08-07

### Enhancements

- Server-side event tracking via `Rybbit::track()->pageView()/event()/performance()/outbound()/error()`, hitting Rybbit's `/api/track` endpoint.
- User queries via `Rybbit::users()->list()/sessionCount()/find()` against Rybbit's read API.
- `Rybbit::fake()` for testing — swaps in an in-memory fake with assertions for every tracking and user-query method, no real HTTP requests made.
- `Rybbit::track()->send()` now auto-fills `pathname`, `hostname`, `user_agent`, `ip_address`, `querystring`, and `user_id` from the current request/authenticated user (all overridable per call). `user_id` resolution is configurable via `RYBBIT_USER_GUARD` and `RYBBIT_USER_KEY`.
- `@rybbit` directive support for `data-tag` and the full set of session replay (`data-replay-*`) script attributes.
- Ships a Laravel Boost `rybbit-development` Agent Skill (`resources/boost/skills/`), auto-installed into consuming apps via `php artisan boost:install`.

### Documentation

- README table of contents, and usage docs for all of the above.

**Full Changelog**: https://github.com/cocosport/rybbit-sdk-for-laravel/compare/v0.1.0...v0.2.0

## [v0.1.0](https://github.com/cocosport/rybbit-sdk-for-laravel/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
