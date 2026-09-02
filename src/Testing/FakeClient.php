<?php

declare(strict_types=1);

namespace Cocosport\Rybbit\Testing;

use Cocosport\Rybbit\Client;

/**
 * @internal
 */
class FakeClient extends Client
{
    /**
     * @var list<array{method: string, path: string, key: string, data: array<string, mixed>}>
     */
    protected array $recorded = [];

    /**
     * @param  array<string, array<string, mixed>>  $stubs  Keyed by symbolic call name, e.g. "pageview", "users.list".
     */
    public function __construct(protected array $stubs = []) {}

    public function post(string $path, array $data = []): ?array
    {
        return $this->record('post', $path, $data);
    }

    public function get(string $path, array $query = []): ?array
    {
        return $this->record('get', $path, $query);
    }

    public function delete(string $path, array $data = []): ?array
    {
        return $this->record('delete', $path, $data);
    }

    public function sitePath(string $suffix): string
    {
        return "api/sites/fake/$suffix";
    }

    /**
     * @return list<array{method: string, path: string, key: string, data: array<string, mixed>}>
     */
    public function recorded(): array
    {
        return $this->recorded;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    protected function record(string $method, string $path, array $data): ?array
    {
        if (isset($data['properties']) && is_string($data['properties'])) {
            $data['properties'] = json_decode($data['properties'], true);
        }

        $key = $this->resolveKey($method, $path, $data);

        $this->recorded[] = compact('method', 'path', 'key', 'data');

        return $this->stubs[$key] ?? $this->defaultResponse($key);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveKey(string $method, string $path, array $data): string
    {
        if ($path === 'api/track') {
            return (string) ($data['type'] ?? 'track');
        }

        if (str_ends_with($path, '/overview/time-series')) {
            return 'overview.timeSeries';
        }

        if (str_ends_with($path, '/overview')) {
            return 'overview.summary';
        }

        if (str_ends_with($path, '/live-user-count')) {
            return 'overview.liveVisitors';
        }

        if (str_ends_with($path, '/metric')) {
            return 'overview.metric';
        }

        if (str_ends_with($path, '/page-titles')) {
            return 'overview.pageTitles';
        }

        if (str_ends_with($path, '/session-count')) {
            return 'users.sessionCount';
        }

        if (str_ends_with($path, '/users')) {
            return 'users.list';
        }

        return $method === 'delete' ? 'users.delete' : 'users.find';
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultResponse(string $key): array
    {
        return match ($key) {
            'overview.summary' => ['data' => []],
            'overview.timeSeries' => ['data' => []],
            'overview.liveVisitors' => ['count' => 0],
            'overview.metric' => ['data' => ['data' => [], 'totalCount' => 0]],
            'overview.pageTitles' => ['data' => []],
            'users.list' => ['data' => [], 'totalCount' => 0, 'page' => 1, 'pageSize' => 100],
            'users.sessionCount' => ['data' => []],
            'users.find' => ['data' => []],
            'users.delete' => ['success' => true],
            default => ['success' => true],
        };
    }
}
