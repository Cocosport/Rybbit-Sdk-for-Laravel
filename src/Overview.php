<?php

declare(strict_types=1);

namespace Cocosport\Rybbit;

use Cocosport\Rybbit\Enums\FilterParameter;
use Cocosport\Rybbit\Enums\TimeBucket;

class Overview
{
    public function __construct(
        protected Client $client,
    ) {}

    /**
     * Get headline KPIs for the configured site over a date range.
     *
     * GET /api/sites/:site/overview
     *
     * Resolves to:
     *
     *     array{
     *         data: array{
     *             sessions: int,
     *             pageviews: int,
     *             users: int,
     *             pages_per_session: float,
     *             bounce_rate: float,
     *             session_duration: float,
     *         },
     *     }
     *
     * @param  string|null  $startDate  YYYY-MM-DD. Omit both dates to query all time.
     * @param  string|null  $endDate  YYYY-MM-DD.
     * @param  string|null  $timeZone  IANA timezone (defaults to the app.timezone config value).
     * @param  array<string, mixed>  $query  Additional query parameters, merged over the ones above —
     *                                       e.g. start_datetime/end_datetime, past_minutes_start/past_minutes_end,
     *                                       or the API's JSON-encoded filters parameter.
     * @return array<string, mixed>|null
     *
     * @see https://rybbit.com/docs/api/overview/overview
     */
    public function summary(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $timeZone = null,
        array $query = [],
    ): ?array {
        return $this->client->get($this->client->sitePath('overview'), array_merge([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'time_zone' => $timeZone ?? config('app.timezone'),
        ], $query));
    }

    /**
     * Get headline KPIs bucketed over time — e.g. daily active users with bucket "day".
     *
     * GET /api/sites/:site/overview/time-series
     *
     * Resolves to:
     *
     *     array{
     *         data: array{
     *             time: string,
     *             sessions: int,
     *             pageviews: int,
     *             users: int,
     *             pages_per_session: float,
     *             bounce_rate: float,
     *             session_duration: float,
     *         }[],
     *     }
     *
     * @param  string|null  $startDate  YYYY-MM-DD. Omit both dates to query all time.
     * @param  string|null  $endDate  YYYY-MM-DD.
     * @param  string|null  $timeZone  IANA timezone (defaults to the app.timezone config value).
     * @param  array<string, mixed>  $query  Additional query parameters, merged over the ones above —
     *                                       e.g. start_datetime/end_datetime, past_minutes_start/past_minutes_end,
     *                                       or the API's JSON-encoded filters parameter.
     * @return array<string, mixed>|null
     *
     * @see https://rybbit.com/docs/api/overview/time-series
     */
    public function timeSeries(
        TimeBucket $bucket = TimeBucket::Day,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $timeZone = null,
        array $query = [],
    ): ?array {
        return $this->client->get($this->client->sitePath('overview/time-series'), array_merge([
            'bucket' => $bucket->value,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'time_zone' => $timeZone ?? config('app.timezone'),
        ], $query));
    }

    /**
     * Get the number of unique visitors active on the configured site right now.
     *
     * GET /api/sites/:site/live-user-count
     *
     * Resolves to:
     *
     *     array{count: int}
     *
     * @param  int  $minutes  Time window, in minutes, to count active visitors over.
     * @return array<string, mixed>|null
     *
     * @see https://rybbit.com/docs/api/overview/live-visitors
     */
    public function liveVisitors(int $minutes = 5): ?array
    {
        return $this->client->get($this->client->sitePath('live-user-count'), [
            'minutes' => $minutes,
        ]);
    }

    /**
     * Break the configured site's traffic down by a single dimension — e.g. pathname,
     * country, browser, or event name.
     *
     * GET /api/sites/:site/metric
     *
     * Resolves to:
     *
     *     array{
     *         data: array{
     *             data: array{
     *                 value: string,
     *                 count: int,
     *                 percentage: float,
     *                 pageviews: int,
     *                 pageviews_percentage: float,
     *                 bounce_rate: float,
     *             }[],
     *             totalCount: int,
     *         },
     *     }
     *
     * @param  FilterParameter  $parameter  The dimension to break down by.
     * @param  int  $limit  Results per page.
     * @param  int  $page  1-indexed page number.
     * @param  string|null  $startDate  YYYY-MM-DD. Omit both dates to query all time.
     * @param  string|null  $endDate  YYYY-MM-DD.
     * @param  string|null  $timeZone  IANA timezone (defaults to the app.timezone config value).
     * @param  array<string, mixed>  $query  Additional query parameters, merged over the ones above —
     *                                       e.g. start_datetime/end_datetime, past_minutes_start/past_minutes_end,
     *                                       or the API's JSON-encoded filters parameter.
     * @return array<string, mixed>|null
     *
     * @see https://rybbit.com/docs/api/overview/metric
     */
    public function metric(
        FilterParameter $parameter,
        int $limit = 100,
        int $page = 1,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $timeZone = null,
        array $query = [],
    ): ?array {
        return $this->client->get($this->client->sitePath('metric'), array_merge([
            'parameter' => $parameter->value,
            'limit' => $limit,
            'page' => $page,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'time_zone' => $timeZone ?? config('app.timezone'),
        ], $query));
    }

    /**
     * Get the configured site's most visited page titles.
     *
     * GET /api/sites/:site/page-titles
     *
     * Resolves to:
     *
     *     array{
     *         data: array{
     *             value: string,
     *             pathname: string,
     *             count: int,
     *             percentage: float,
     *             pageviews: int,
     *             bounce_rate: float,
     *             time_on_page_seconds: float,
     *         }[],
     *         totalCount?: int,
     *     }
     *
     * @param  int  $limit  Maximum number of page titles to return.
     * @param  int|null  $page  1-indexed page number. When given, the response is paginated and includes `totalCount`.
     * @param  string|null  $startDate  YYYY-MM-DD. Omit both dates to query all time.
     * @param  string|null  $endDate  YYYY-MM-DD.
     * @param  string|null  $timeZone  IANA timezone (defaults to the app.timezone config value).
     * @param  array<string, mixed>  $query  Additional query parameters, merged over the ones above —
     *                                       e.g. start_datetime/end_datetime, past_minutes_start/past_minutes_end,
     *                                       or the API's JSON-encoded filters parameter.
     * @return array<string, mixed>|null
     *
     * @see https://rybbit.com/docs/api/overview/page-titles
     */
    public function pageTitles(
        int $limit = 10,
        ?int $page = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $timeZone = null,
        array $query = [],
    ): ?array {
        return $this->client->get($this->client->sitePath('page-titles'), array_merge([
            'limit' => $limit,
            'page' => $page,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'time_zone' => $timeZone ?? config('app.timezone'),
        ], $query));
    }
}
