<?php

namespace Cocosport\Rybbit;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ForwardTunnelData implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $headers
     */
    public function __construct(
        protected string $url,
        protected string $method,
        protected array $data,
        protected array $headers,
    ) {}

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $pendingRequest = Http::withHeaders($this->headers)
            ->retry(5, 3000);

        if ($this->method === 'POST') {
            $response = $pendingRequest->post($this->url, $this->data);
        } else {
            $response = $pendingRequest->get($this->url, $this->data);
        }

        if ($response->failed() && config('rybbit.logs')) {
            Log::error('Failed to forward data to Rybbit', [
                'url' => $this->url,
                'data' => $this->data,
                'headers' => $this->headers,
                'response' => $response->body(),
            ]);
        }

        $response->throwIf(config('rybbit.throw_on_error'));
    }
}
