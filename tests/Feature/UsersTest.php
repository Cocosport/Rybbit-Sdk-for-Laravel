<?php

declare(strict_types=1);

use Cocosport\Rybbit\Enums\SortOrder;
use Cocosport\Rybbit\Enums\UserSortBy;
use Cocosport\Rybbit\Rybbit;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('rybbit.host', 'https://app.rybbit.io');
    config()->set('rybbit.site_seq_id', '1');
    config()->set('rybbit.api_key', 'test-api-key');
});

it('lists users for the configured site', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/users*' => Http::response([
        'data' => [],
        'totalCount' => 0,
        'page' => 2,
        'pageSize' => 10,
    ])]);

    $response = app(Rybbit::class)->users()->list(page: 2, sortBy: UserSortBy::Pageviews, sortOrder: SortOrder::Asc);

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://app.rybbit.io/api/sites/1/users')
        && $request->method() === 'GET'
        && $request->hasHeader('Authorization', 'Bearer test-api-key')
        && $request['page'] == 2
        && $request['sort_by'] === 'pageviews'
        && $request['sort_order'] === 'asc');

    expect($response['totalCount'])->toBe(0);
});

it('gets the daily session count for a user', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/users/session-count*' => Http::response([
        'data' => [['date' => '2024-01-15', 'sessions' => 2]],
    ])]);

    $response = app(Rybbit::class)->users()->sessionCount('abc123def456', ['time_zone' => 'America/New_York']);

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://app.rybbit.io/api/sites/1/users/session-count')
        && $request['user_id'] === 'abc123def456'
        && $request['time_zone'] === 'America/New_York');

    expect($response['data'][0]['sessions'])->toBe(2);
});

it('defaults the time zone to the app.timezone config value', function () {
    config()->set('app.timezone', 'Europe/Rome');
    Http::fake(['https://app.rybbit.io/api/sites/1/users/session-count*' => Http::response(['data' => []])]);

    app(Rybbit::class)->users()->sessionCount('abc123def456');

    Http::assertSent(fn ($request) => $request['user_id'] === 'abc123def456'
        && $request['time_zone'] === 'Europe/Rome');
});

it('gets detailed info for a user', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/users/*' => Http::response([
        'data' => ['user_id' => 'abc123def456', 'identified_user_id' => 'user@example.com'],
    ])]);

    $response = app(Rybbit::class)->users()->find('user@example.com');

    Http::assertSent(fn ($request) => $request->url() === 'https://app.rybbit.io/api/sites/1/users/user%40example.com'
        && $request->method() === 'GET');

    expect($response['data']['identified_user_id'])->toBe('user@example.com');
});

it('deletes a user', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/users/*' => Http::response(['success' => true])]);

    $response = app(Rybbit::class)->users()->delete('user@example.com');

    Http::assertSent(fn ($request) => $request->url() === 'https://app.rybbit.io/api/sites/1/users/user%40example.com'
        && $request->method() === 'DELETE');

    expect($response['success'])->toBeTrue();
});
