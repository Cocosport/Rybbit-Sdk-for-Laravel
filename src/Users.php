<?php

declare(strict_types=1);

namespace Cocosport\Rybbit;

class Users
{
    public function __construct(
        protected Client $client,
    ) {}

    /**
     * List users for the configured site.
     *
     * GET /api/sites/:site/users
     *
     * Resolves to:
     *
     *     array{
     *         data: array{
     *             user_id: string,
     *             identified_user_id: string,
     *             traits: array<string, mixed>|null,
     *             country: string,
     *             region: string,
     *             city: string,
     *             language: string,
     *             browser: string,
     *             operating_system: string,
     *             device_type: "desktop"|"mobile"|"tablet",
     *             pageviews: int,
     *             events: int,
     *             sessions: int,
     *             hostname: string,
     *             first_seen: string,
     *             last_seen: string,
     *         }[],
     *         totalCount: int,
     *         page: int,
     *         pageSize: int,
     *     }
     *
     * @param  array{
     *     page?: int,
     *     page_size?: int,
     *     sort_by?: "first_seen"|"last_seen"|"pageviews"|"sessions"|"events",
     *     sort_order?: "asc"|"desc",
     *     identified_only?: "true"|"false",
     *     ...
     * }  $query  page: 1-indexed page number (default 1).
     *            page_size: results per page (default 100).
     *            sort_by: field to sort by (default "last_seen").
     *            sort_order: sort direction (default "desc").
     *            identified_only: only return users with an identified_user_id (default "false").
     *            Also accepts the API's common parameters (date range, timezone, filters, ...).
     * @return array<string, mixed>|null
     *
     * @see https://rybbit.com/docs/api/users/list
     */
    public function list(array $query = []): ?array
    {
        return $this->client->get($this->path('users'), $query);
    }

    /**
     * Get the number of sessions per day for a specific user.
     *
     * GET /api/sites/:site/users/session-count
     *
     * Resolves to:
     *
     *     array{
     *         data: array{
     *             date: string,
     *             sessions: int,
     *         }[],
     *     }
     *
     * @param  string  $userId  Device fingerprint or identified user ID.
     * @param  array{
     *     time_zone?: string,
     * }  $query  time_zone: IANA timezone used for date grouping (defaults to the app.timezone config value).
     * @return array<string, mixed>|null
     *
     * @see https://rybbit.com/docs/api/users/session-count
     */
    public function sessionCount(string $userId, array $query = []): ?array
    {
        return $this->client->get(
            $this->path('users/session-count'),
            array_merge([
                'time_zone' => config('app.timezone'),
            ], $query, [
                'user_id' => $userId,
            ]),
        );
    }

    /**
     * Get detailed information about a specific user: profile traits, locations, devices, and linked devices.
     *
     * GET /api/sites/:site/users/:userId
     *
     * Resolves to:
     *
     *     array{
     *         data: array{
     *             user_id: string,
     *             identified_user_id: string,
     *             traits: array<string, mixed>|null,
     *             sessions: int,
     *             pageviews: int,
     *             events: int,
     *             duration: int,
     *             country: string,
     *             region: string,
     *             city: string,
     *             language: string,
     *             browser: string,
     *             browser_version: string,
     *             operating_system: string,
     *             operating_system_version: string,
     *             device_type: "desktop"|"mobile"|"tablet",
     *             screen_width: int,
     *             screen_height: int,
     *             first_seen: string,
     *             last_seen: string,
     *             timezone: string,
     *             ip: string,
     *             first_referrer: string,
     *             first_channel: string,
     *             first_entry_page: string,
     *             first_utm_source: string,
     *             first_utm_medium: string,
     *             first_utm_campaign: string,
     *             last_referrer: string,
     *             last_channel: string,
     *             vitals: array{
     *                 lcp_p75: float|int|null,
     *                 cls_p75: float|int|null,
     *                 inp_p75: float|int|null,
     *                 fcp_p75: float|int|null,
     *                 ttfb_p75: float|int|null,
     *                 performance_events: int,
     *             }|null,
     *             locations: array{
     *                 country: string,
     *                 region: string,
     *                 city: string,
     *                 sessions: int,
     *                 last_seen: string,
     *             }[],
     *             devices: array{
     *                 device_type: string,
     *                 browser: string,
     *                 browser_version: string,
     *                 operating_system: string,
     *                 operating_system_version: string,
     *                 screen_width: int,
     *                 screen_height: int,
     *                 sessions: int,
     *                 last_seen: string,
     *             }[],
     *             linked_devices: array{
     *                 anonymous_id: string,
     *                 created_at: string,
     *             }[],
     *         },
     *     }
     *
     * @param  string  $userId  Device fingerprint or identified user ID.
     * @return array<string, mixed>|null
     *
     * @see https://rybbit.com/docs/api/users/info
     */
    public function find(string $userId): ?array
    {
        return $this->client->get($this->path('users/'.rawurlencode($userId)));
    }

    private function path(string $suffix): string
    {
        return 'api/sites/'.config('rybbit.site_seq_id')."/$suffix";
    }
}
