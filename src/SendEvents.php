<?php

declare(strict_types=1);

namespace Cocosport\Rybbit;

use Illuminate\Http\Client\Response;

class SendEvents
{
    public function __construct(
        protected Client $client,
    ) {}

    /**
     * Send a tracking event to Rybbit's /api/track endpoint.
     *
     * @param  string  $type  "pageview" | "custom_event" | "performance" | "outbound" | "error"
     * @param  array{
     *     pathname?: string,
     *     hostname?: string,
     *     page_title?: string,
     *     referrer?: string,
     *     user_id?: string,
     *     user_agent?: string,
     *     ip_address?: string,
     *     querystring?: string,
     *     language?: string,
     *     screenWidth?: int,
     *     screenHeight?: int,
     *     event_name?: string,
     *     properties?: array<string, mixed>|string,
     *     feature_flags?: array<string, string>,
     *     ...
     * }  $data  Additional /api/track request fields, merged over the site_id/type defaults.
     *
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function send(string $type, array $data = []): ?Response
    {
        $payload = array_merge([
            'site_id' => (string) config('rybbit.site_seq_id'),
            'type' => $type,
        ], $data);

        if (isset($payload['properties']) && is_array($payload['properties'])) {
            $payload['properties'] = json_encode($payload['properties']);
        }

        return $this->client->post('api/track', $payload);
    }

    /**
     * Track a page view.
     *
     * @param  array{
     *     pathname?: string,
     *     hostname?: string,
     *     page_title?: string,
     *     referrer?: string,
     *     user_id?: string,
     *     user_agent?: string,
     *     ip_address?: string,
     *     querystring?: string,
     *     language?: string,
     *     screenWidth?: int,
     *     screenHeight?: int,
     *     feature_flags?: array<string, string>,
     *     ...
     * }  $data
     */
    public function pageView(array $data = []): ?Response
    {
        return $this->send('pageview', $data);
    }

    /**
     * Track a custom event.
     *
     * @param  string  $eventName  Name of the custom event, max 256 chars.
     * @param  array<string, mixed>  $properties  Arbitrary event data, encoded as a JSON string, max 2048 chars.
     * @param  array{
     *     pathname?: string,
     *     hostname?: string,
     *     page_title?: string,
     *     referrer?: string,
     *     user_id?: string,
     *     user_agent?: string,
     *     ip_address?: string,
     *     querystring?: string,
     *     language?: string,
     *     screenWidth?: int,
     *     screenHeight?: int,
     *     feature_flags?: array<string, string>,
     *     ...
     * }  $data
     */
    public function event(string $eventName, array $properties = [], array $data = []): ?Response
    {
        return $this->send('custom_event', array_merge($data, [
            'event_name' => $eventName,
            'properties' => $properties,
        ]));
    }

    /**
     * Track Core Web Vitals.
     *
     * @param  array{
     *     lcp?: float|int|null,
     *     cls?: float|int|null,
     *     inp?: float|int|null,
     *     fcp?: float|int|null,
     *     ttfb?: float|int|null,
     * }  $metrics
     * @param  array{
     *     pathname?: string,
     *     hostname?: string,
     *     page_title?: string,
     *     referrer?: string,
     *     user_id?: string,
     *     user_agent?: string,
     *     ip_address?: string,
     *     querystring?: string,
     *     language?: string,
     *     screenWidth?: int,
     *     screenHeight?: int,
     *     feature_flags?: array<string, string>,
     *     ...
     * }  $data
     */
    public function performance(array $metrics, array $data = []): ?Response
    {
        return $this->send('performance', array_merge($data, $metrics));
    }

    /**
     * Track an outbound link click.
     *
     * @param  string  $url  Destination URL, required.
     * @param  array{
     *     text?: string,
     *     target?: string,
     *     ...
     * }  $properties
     * @param  array{
     *     pathname?: string,
     *     hostname?: string,
     *     page_title?: string,
     *     referrer?: string,
     *     user_id?: string,
     *     user_agent?: string,
     *     ip_address?: string,
     *     querystring?: string,
     *     language?: string,
     *     screenWidth?: int,
     *     screenHeight?: int,
     *     feature_flags?: array<string, string>,
     *     ...
     * }  $data
     */
    public function outbound(string $url, array $properties = [], array $data = []): ?Response
    {
        return $this->send('outbound', array_merge($data, [
            'properties' => array_merge(['url' => $url], $properties),
        ]));
    }

    /**
     * Track a JavaScript error.
     *
     * @param  string  $eventName  The error type (e.g. "TypeError"), sent as event_name.
     * @param  string  $message  Error message, max 500 chars.
     * @param  array{
     *     stack?: string,
     *     fileName?: string,
     *     lineNumber?: int,
     *     columnNumber?: int,
     *     ...
     * }  $properties
     * @param  array{
     *     pathname?: string,
     *     hostname?: string,
     *     page_title?: string,
     *     referrer?: string,
     *     user_id?: string,
     *     user_agent?: string,
     *     ip_address?: string,
     *     querystring?: string,
     *     language?: string,
     *     screenWidth?: int,
     *     screenHeight?: int,
     *     feature_flags?: array<string, string>,
     *     ...
     * }  $data
     */
    public function error(string $eventName, string $message, array $properties = [], array $data = []): ?Response
    {
        return $this->send('error', array_merge($data, [
            'event_name' => $eventName,
            'properties' => array_merge(['message' => $message], $properties),
        ]));
    }
}
