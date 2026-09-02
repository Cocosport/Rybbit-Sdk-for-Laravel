<?php

declare(strict_types=1);

use Cocosport\Rybbit\Enums\FilterParameter;
use Cocosport\Rybbit\Enums\TimeBucket;
use Cocosport\Rybbit\Rybbit;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('rybbit.host', 'https://app.rybbit.io');
    config()->set('rybbit.site_seq_id', '1');
    config()->set('rybbit.api_key', 'test-api-key');
});

it('gets the overview summary for the configured site', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/overview*' => Http::response([
        'data' => ['sessions' => 100, 'pageviews' => 200, 'users' => 80],
    ])]);

    $response = app(Rybbit::class)->overview()->summary(startDate: '2024-01-01', endDate: '2024-01-07');

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://app.rybbit.io/api/sites/1/overview?')
        && ! str_contains($request->url(), 'time-series')
        && $request['start_date'] === '2024-01-01'
        && $request['end_date'] === '2024-01-07'
        && $request->hasHeader('Authorization', 'Bearer test-api-key'));

    expect($response['data']['users'])->toBe(80);
});

it('gets a bucketed time series for the configured site', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/overview/time-series*' => Http::response([
        'data' => [['time' => '2024-01-01 00:00:00', 'users' => 42]],
    ])]);

    $response = app(Rybbit::class)->overview()->timeSeries(TimeBucket::Day, startDate: '2024-01-01', endDate: '2024-01-07');

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://app.rybbit.io/api/sites/1/overview/time-series')
        && $request['bucket'] === 'day'
        && $request['start_date'] === '2024-01-01'
        && $request['end_date'] === '2024-01-07');

    expect($response['data'][0]['users'])->toBe(42);
});

it('defaults bucket to "day" and time zone to the app.timezone config value', function () {
    config()->set('app.timezone', 'Europe/Rome');
    Http::fake(['https://app.rybbit.io/api/sites/1/overview/time-series*' => Http::response(['data' => []])]);

    app(Rybbit::class)->overview()->timeSeries();

    Http::assertSent(fn ($request) => $request['bucket'] === 'day' && $request['time_zone'] === 'Europe/Rome');
});

it('gets the live visitor count for the configured site', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/live-user-count*' => Http::response(['count' => 42])]);

    $response = app(Rybbit::class)->overview()->liveVisitors(10);

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://app.rybbit.io/api/sites/1/live-user-count')
        && $request['minutes'] == 10);

    expect($response['count'])->toBe(42);
});

it('defaults the live visitors window to 5 minutes', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/live-user-count*' => Http::response(['count' => 0])]);

    app(Rybbit::class)->overview()->liveVisitors();

    Http::assertSent(fn ($request) => $request['minutes'] == 5);
});

it('breaks down traffic by a dimension', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/metric*' => Http::response([
        'data' => ['data' => [['value' => 'US', 'count' => 10]], 'totalCount' => 1],
    ])]);

    $response = app(Rybbit::class)->overview()->metric(FilterParameter::Country, startDate: '2024-01-01', endDate: '2024-01-07');

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://app.rybbit.io/api/sites/1/metric')
        && $request['parameter'] === 'country'
        && $request['limit'] == 100
        && $request['page'] == 1
        && $request['start_date'] === '2024-01-01'
        && $request['end_date'] === '2024-01-07');

    expect($response['data']['totalCount'])->toBe(1);
});

it('gets the configured site\'s page titles', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/page-titles*' => Http::response([
        'data' => [['value' => 'Home', 'pathname' => '/', 'count' => 10]],
    ])]);

    $response = app(Rybbit::class)->overview()->pageTitles(limit: 5);

    Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://app.rybbit.io/api/sites/1/page-titles')
        && $request['limit'] == 5
        && ! str_contains($request->url(), 'page='));

    expect($response['data'][0]['value'])->toBe('Home');
});

it('paginates page titles when a page is given', function () {
    Http::fake(['https://app.rybbit.io/api/sites/1/page-titles*' => Http::response([
        'data' => [],
        'totalCount' => 0,
    ])]);

    app(Rybbit::class)->overview()->pageTitles(page: 2);

    Http::assertSent(fn ($request) => $request['page'] == 2);
});
