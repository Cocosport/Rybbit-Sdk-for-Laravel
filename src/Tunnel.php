<?php

namespace Cocosport\Rybbit;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class Tunnel
{
    private string $rybbitHost;

    public function __construct()
    {
        $this->rybbitHost = config('rybbit.host');
    }

    public function forward(
        string $path,
        string $method,
        Request $request,
        bool $shouldQueue = false,
    ): Response {
        $url = "$this->rybbitHost/$path";

        $forwardedFor = $request->header('X-Forwarded-For', $request->ip());
        $clientIp = $this->normalizeClientIp($forwardedFor, (string) $request->ip());

        $headers = [
            'X-Real-IP' => $clientIp,
            'X-Forwarded-For' => $clientIp,
            'User-Agent' => $request->userAgent(),
            'Referer' => $request->header('Referer', ''),
            'Accept' => $request->header('Accept', ''),
            'Accept-Language' => $request->header('Accept-Language', ''),
        ];

        if ($shouldQueue) {
            return $this->queue($url, $method, $request, $headers);
        }

        $httpRequest = Http::timeout(30)
            ->retry(3, 1000, throw: false)
            ->withHeaders($headers);

        try {
            $response = $method === 'POST'
                ? $httpRequest->post($url, $request->all())
                : $httpRequest->get($url);
        } catch (ConnectionException $exception) {
            if (config('rybbit.logs')) {
                Log::warning('Rybbit tunnel connection error: '.$exception->getMessage(), [
                    'data' => $request->all(),
                    'headers' => $headers,
                ]);
            }

            return response()->json(['error' => 'Rybbit tunnel error'], 500);
        }

        if ($response->failed() && config('rybbit.logs')) {
            Log::warning('Rybbit tunnel request failed: '.$response->status(), [
                'data' => $request->all(),
                'headers' => $headers,
                'response' => $response->body(),
            ]);
        }

        $response->throwIf(config('rybbit.throw_on_error'));

        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type'));
    }

    /**
     * @param  list<string>|string|null  $xForwardedFor
     */
    private function normalizeClientIp(array|string|null $xForwardedFor, string $fallbackIp): string
    {
        $candidates = [];

        foreach (Arr::wrap($xForwardedFor) as $value) {
            foreach (explode(',', $value) as $part) {
                $ip = trim($part);

                if ($ip !== '') {
                    $candidates[] = $ip;
                }
            }
        }

        $candidates[] = trim($fallbackIp);

        foreach ($candidates as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return $fallbackIp;
    }

    /**
     * @param  array<string, string|list<string>|null>  $headers
     */
    protected function queue(string $url, string $method, Request $request, array $headers): JsonResponse
    {
        $job = new ForwardTunnelData($url, $method, $request->all(), $headers);

        dispatch($job)->onQueue(config('rybbit.queue'));

        return response()->json([
            'success' => true,
        ]);
    }
}
