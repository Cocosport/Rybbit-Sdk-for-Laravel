<?php

namespace Cocosport\Rybbit\Http\Controllers;

use Closure;
use Cocosport\Rybbit\Exceptions\TunnelDisabledException;
use Cocosport\Rybbit\Tunnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RybbitTunnelController
{
    public function __construct(
        protected Tunnel $tunnel,
    ) {
        throw_unless(config('rybbit.tunnel.enabled'), TunnelDisabledException::class);
    }

    public function proxyScript(Request $request, string $script): Response
    {
        $cacheKey = $this->getCacheKey("script_$script");

        return $this->rememberSuccessful($cacheKey, fn () => $this->tunnel->forward("api/$script", 'GET', $request));
    }

    public function proxyTrack(Request $request): Response
    {
        return $this->tunnel->forward('api/track', 'POST', $request);
    }

    public function proxyIdentify(Request $request): Response
    {
        return $this->tunnel->forward('api/identify', 'POST', $request);
    }

    public function proxySessionReplay(Request $request, string $siteId): Response
    {
        return $this->tunnel->forward("api/session-replay/record/$siteId", 'POST', $request, shouldQueue: true);
    }

    public function proxySiteConfig(Request $request, string $siteId): Response
    {
        $cacheKey = $this->getCacheKey("config_$siteId");

        return $this->rememberSuccessful(
            $cacheKey,
            fn () => $this->tunnel->forward("api/site/tracking-config/$siteId", 'GET', $request),
        );
    }

    protected function getCacheKey(string $key): string
    {
        return config('rybbit.tunnel.cache-key-prefix').$key;
    }

    protected function rememberSuccessful(string $key, Closure $callback): Response
    {
        /** @var array{content: string, status: int, contentType: string|null}|null $cached */
        $cached = Cache::get($key);

        if (is_array($cached)) {
            return response($cached['content'], $cached['status'])
                ->header('Content-Type', $cached['contentType']);
        }

        $response = $callback();

        if ($response->isSuccessful()) {
            Cache::put($key, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'contentType' => $response->headers->get('Content-Type'),
            ], 3600);
        }

        return $response;
    }
}
