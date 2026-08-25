<?php

declare(strict_types=1);

use Cocosport\Rybbit\Facades\Rybbit;
use Cocosport\Rybbit\Testing\RybbitFake;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\AssertionFailedError;

it('returns a fake instance and prevents real http requests', function () {
    $fake = Rybbit::fake();

    expect($fake)->toBeInstanceOf(RybbitFake::class);

    Rybbit::track()->pageView(['pathname' => '/checkout']);
    Rybbit::users()->list();

    Http::assertNothingSent();
});

it('asserts page views were sent', function () {
    Rybbit::fake();

    Rybbit::track()->pageView(['pathname' => '/checkout']);

    Rybbit::assertPageViewSent(fn (array $data) => $data['pathname'] === '/checkout');
});

it('asserts custom events were sent with decoded properties', function () {
    Rybbit::fake();

    Rybbit::track()->event('purchase', ['amount' => 99.99, 'currency' => 'USD']);

    Rybbit::assertEventSent('purchase', fn (array $data) => $data['properties']['amount'] === 99.99);
});

it('asserts performance metrics were sent', function () {
    Rybbit::fake();

    Rybbit::track()->performance(['lcp' => 1200.5]);

    Rybbit::assertPerformanceSent(fn (array $data) => $data['lcp'] === 1200.5);
});

it('asserts outbound clicks were sent', function () {
    Rybbit::fake();

    Rybbit::track()->outbound('https://example.com', ['text' => 'Example']);

    Rybbit::assertOutboundSent('https://example.com');
});

it('asserts errors were sent', function () {
    Rybbit::fake();

    Rybbit::track()->error('TypeError', 'Cannot read property of undefined');

    Rybbit::assertErrorSent('TypeError', fn (array $data) => $data['properties']['message'] === 'Cannot read property of undefined');
});

it('asserts nothing was sent', function () {
    Rybbit::fake();

    Rybbit::assertNothingSent();
});

it('asserts the number of requests sent', function () {
    Rybbit::fake();

    Rybbit::track()->pageView();
    Rybbit::track()->event('purchase');

    Rybbit::assertSentCount(2);
});

it('asserts via the generic assertSent escape hatch', function () {
    Rybbit::fake();

    Rybbit::track()->pageView(['pathname' => '/checkout']);

    Rybbit::assertSent(fn (string $key, array $data) => $key === 'pageview' && $data['pathname'] === '/checkout');
});

it('asserts a users list request was sent', function () {
    Rybbit::fake();

    Rybbit::users()->list(sortBy: 'pageviews');

    Rybbit::assertUsersListed(fn (array $query) => $query['sort_by'] === 'pageviews');
});

it('asserts a specific user was requested', function () {
    Rybbit::fake();

    Rybbit::users()->find('user@example.com');

    Rybbit::assertUserRequested('user@example.com');
});

it('asserts a specific user was deleted', function () {
    Rybbit::fake();

    Rybbit::users()->delete('user@example.com');

    Rybbit::assertUserDeleted('user@example.com');
});

it('asserts a session count request was sent', function () {
    Rybbit::fake();

    Rybbit::users()->sessionCount('abc123def456', ['time_zone' => 'UTC']);

    Rybbit::assertSessionCountRequested('abc123def456', fn (array $query) => $query['time_zone'] === 'UTC');
});

it('returns sensible default responses when nothing is stubbed', function () {
    Rybbit::fake();

    expect(Rybbit::track()->pageView())->toBe(['success' => true]);
    expect(Rybbit::users()->list())->toBe(['data' => [], 'totalCount' => 0, 'page' => 1, 'pageSize' => 100]);
    expect(Rybbit::users()->find('abc123'))->toBe(['data' => []]);
    expect(Rybbit::users()->delete('abc123'))->toBe(['success' => true]);
});

it('returns stubbed responses keyed by symbolic call name', function () {
    Rybbit::fake([
        'users.find' => ['data' => ['user_id' => 'abc123', 'identified_user_id' => 'user@example.com']],
    ]);

    $response = Rybbit::users()->find('user@example.com');

    expect($response['data']['identified_user_id'])->toBe('user@example.com');
});

it('fails the assertion when no matching request was sent', function () {
    Rybbit::fake();

    Rybbit::track()->pageView();

    Rybbit::assertEventSent('purchase');
})->throws(AssertionFailedError::class);
