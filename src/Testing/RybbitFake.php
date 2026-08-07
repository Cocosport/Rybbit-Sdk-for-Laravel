<?php

declare(strict_types=1);

namespace Cocosport\Rybbit\Testing;

use Closure;
use Cocosport\Rybbit\Rybbit;
use Cocosport\Rybbit\SendEvents;
use Cocosport\Rybbit\Users;
use PHPUnit\Framework\Assert as PHPUnit;

class RybbitFake extends Rybbit
{
    protected FakeClient $client;

    /**
     * @param  array<string, array<string, mixed>>  $stubs  Keyed by symbolic call name, e.g. "pageview", "users.list".
     */
    public function __construct(array $stubs = [])
    {
        $this->client = new FakeClient($stubs);
    }

    public function track(): SendEvents
    {
        return new SendEvents($this->client);
    }

    public function users(): Users
    {
        return new Users($this->client);
    }

    public function assertNothingSent(): void
    {
        PHPUnit::assertEmpty(
            $this->client->recorded(),
            'Expected no requests to be sent to Rybbit, but '.count($this->client->recorded()).' were sent.',
        );
    }

    public function assertSentCount(int $count): void
    {
        PHPUnit::assertCount($count, $this->client->recorded());
    }

    /**
     * @param  Closure(string $key, array<string, mixed> $data): bool  $callback
     */
    public function assertSent(Closure $callback): void
    {
        PHPUnit::assertTrue(
            $this->matching($callback) !== [],
            'No matching request was sent to Rybbit.',
        );
    }

    /**
     * @param  Closure(array<string, mixed> $data): bool|null  $callback
     */
    public function assertPageViewSent(?Closure $callback = null): void
    {
        $this->assertTrackTypeSent('pageview', $callback);
    }

    /**
     * @param  Closure(array<string, mixed> $data): bool|null  $callback
     */
    public function assertEventSent(string $eventName, ?Closure $callback = null): void
    {
        $matching = $this->matching(fn (string $key, array $data) => $key === 'custom_event'
            && ($data['event_name'] ?? null) === $eventName
            && (! $callback || $callback($data)));

        PHPUnit::assertNotEmpty($matching, "No custom event [{$eventName}] was sent to Rybbit.");
    }

    /**
     * @param  Closure(array<string, mixed> $data): bool|null  $callback
     */
    public function assertPerformanceSent(?Closure $callback = null): void
    {
        $this->assertTrackTypeSent('performance', $callback);
    }

    /**
     * @param  Closure(array<string, mixed> $data): bool|null  $callback
     */
    public function assertOutboundSent(string $url, ?Closure $callback = null): void
    {
        $matching = $this->matching(fn (string $key, array $data) => $key === 'outbound'
            && ($data['properties']['url'] ?? null) === $url
            && (! $callback || $callback($data)));

        PHPUnit::assertNotEmpty($matching, "No outbound click to [{$url}] was sent to Rybbit.");
    }

    /**
     * @param  Closure(array<string, mixed> $data): bool|null  $callback
     */
    public function assertErrorSent(string $eventName, ?Closure $callback = null): void
    {
        $matching = $this->matching(fn (string $key, array $data) => $key === 'error'
            && ($data['event_name'] ?? null) === $eventName
            && (! $callback || $callback($data)));

        PHPUnit::assertNotEmpty($matching, "No error [{$eventName}] was sent to Rybbit.");
    }

    /**
     * @param  Closure(array<string, mixed> $query): bool|null  $callback
     */
    public function assertUsersListed(?Closure $callback = null): void
    {
        $matching = $this->matching(fn (string $key, array $data) => $key === 'users.list'
            && (! $callback || $callback($data)));

        PHPUnit::assertNotEmpty($matching, 'No users list request was sent to Rybbit.');
    }

    public function assertUserRequested(string $userId): void
    {
        $matching = array_filter($this->client->recorded(), fn (array $call) => $call['key'] === 'users.find'
            && rawurldecode(basename($call['path'])) === $userId);

        PHPUnit::assertNotEmpty($matching, "No user [{$userId}] was requested from Rybbit.");
    }

    /**
     * @param  Closure(array<string, mixed> $query): bool|null  $callback
     */
    public function assertSessionCountRequested(string $userId, ?Closure $callback = null): void
    {
        $matching = $this->matching(fn (string $key, array $data) => $key === 'users.sessionCount'
            && ($data['user_id'] ?? null) === $userId
            && (! $callback || $callback($data)));

        PHPUnit::assertNotEmpty($matching, "No session count request for user [{$userId}] was sent to Rybbit.");
    }

    /**
     * @param  Closure(array<string, mixed> $data): bool|null  $callback
     */
    protected function assertTrackTypeSent(string $type, ?Closure $callback): void
    {
        $matching = $this->matching(fn (string $key, array $data) => $key === $type && (! $callback || $callback($data)));

        PHPUnit::assertNotEmpty($matching, "No [{$type}] event was sent to Rybbit.");
    }

    /**
     * @param  Closure(string $key, array<string, mixed> $data): bool  $callback
     * @return list<array{method: string, path: string, key: string, data: array<string, mixed>}>
     */
    protected function matching(Closure $callback): array
    {
        return array_values(array_filter(
            $this->client->recorded(),
            fn (array $call) => $callback($call['key'], $call['data']),
        ));
    }
}
