<?php

declare(strict_types=1);

namespace Cocosport\Rybbit\Facades;

use Cocosport\Rybbit\Testing\RybbitFake;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Cocosport\Rybbit\SendEvents track()
 * @method static \Cocosport\Rybbit\Users users()
 * @method static void assertNothingSent()
 * @method static void assertSentCount(int $count)
 * @method static void assertSent(\Closure $callback)
 * @method static void assertPageViewSent(?\Closure $callback = null)
 * @method static void assertEventSent(string $eventName, ?\Closure $callback = null)
 * @method static void assertPerformanceSent(?\Closure $callback = null)
 * @method static void assertOutboundSent(string $url, ?\Closure $callback = null)
 * @method static void assertErrorSent(string $eventName, ?\Closure $callback = null)
 * @method static void assertUsersListed(?\Closure $callback = null)
 * @method static void assertUserRequested(string $userId)
 * @method static void assertSessionCountRequested(string $userId, ?\Closure $callback = null)
 *
 * @see \Cocosport\Rybbit\Rybbit
 * @see RybbitFake
 */
class Rybbit extends Facade
{
    /**
     * Replace the bound instance with a fake that records everything sent to Rybbit
     * instead of making real HTTP requests.
     *
     * @param  array<string, array<string, mixed>>  $stubs  Keyed by symbolic call name, e.g. "pageview", "users.list".
     */
    public static function fake(array $stubs = []): RybbitFake
    {
        static::swap($fake = new RybbitFake($stubs));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Cocosport\Rybbit\Rybbit::class;
    }
}
