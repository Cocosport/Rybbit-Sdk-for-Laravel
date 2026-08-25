<?php

declare(strict_types=1);

namespace Cocosport\Rybbit;

use Cocosport\Rybbit\Exceptions\InvalidConfigurationException;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @internal
 */
#[Singleton]
class Client
{
    private readonly ?string $host;

    private readonly ?string $apiKey;

    private readonly ?string $siteSeqId;

    private readonly bool $logsEnabled;

    private readonly bool $throwOnError;

    private bool $misconfigured = false;

    /**
     * @throws InvalidConfigurationException
     */
    public function __construct()
    {
        $this->host = config('rybbit.host');
        $this->apiKey = config('rybbit.api_key');
        $this->siteSeqId = config('rybbit.site_seq_id');
        $this->logsEnabled = (bool) config('rybbit.logs');
        $this->throwOnError = (bool) config('rybbit.throw_on_error');

        $this->validateConfiguration();
    }

    /**
     * Send a POST request to Rybbit's API and return the decoded JSON response.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function post(string $path, array $data = []): ?array
    {
        return $this->request('post', $path, $data)?->json();
    }

    /**
     * Send a GET request to Rybbit's API and return the decoded JSON response.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>|null
     */
    public function get(string $path, array $query = []): ?array
    {
        return $this->request('get', $path, $query)?->json();
    }

    /**
     * Send a DELETE request to Rybbit's API and return the decoded JSON response.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function delete(string $path, array $data = []): ?array
    {
        return $this->request('delete', $path, $data)?->json();
    }

    /**
     * Build a path scoped to the configured site, e.g. "api/sites/1/users".
     */
    public function sitePath(string $suffix): string
    {
        return "api/sites/$this->siteSeqId/$suffix";
    }

    /**
     * @throws InvalidConfigurationException
     */
    private function validateConfiguration(): void
    {
        try {
            if (blank($this->host)) {
                throw InvalidConfigurationException::missingHost();
            }

            if (filter_var($this->host, FILTER_VALIDATE_URL) === false) {
                throw InvalidConfigurationException::invalidHost($this->host);
            }

            if (blank($this->siteSeqId)) {
                throw InvalidConfigurationException::missingSiteSeqId();
            }
        } catch (InvalidConfigurationException $exception) {
            if (! app()->isProduction()) {
                throw $exception;
            }

            if ($this->logsEnabled) {
                Log::error($exception->getMessage());
            }

            $this->misconfigured = true;
        }

        if (blank($this->apiKey) && $this->logsEnabled) {
            Log::warning('The "rybbit.api_key" configuration value is missing. Server-side tracking will be subject to bot detection and domain validation. Set RYBBIT_API_KEY in your .env file to bypass this.');
        }
    }

    /**
     * Make the actual HTTP request and handle its response.
     *
     * @param  string  $method  "post" | "get" | "delete"
     * @param  array<string, mixed>  $data  Request body (POST) or query parameters (GET).
     */
    private function request(string $method, string $path, array $data): ?Response
    {
        if ($this->misconfigured) {
            return null;
        }

        $request = filled($this->apiKey) ? Http::withToken($this->apiKey) : Http::asJson();

        try {
            $response = $request->$method("$this->host/$path", $data);
        } catch (ConnectionException $exception) {
            if ($this->logsEnabled) {
                Log::warning("Rybbit $path connection error: ".$exception->getMessage(), [
                    'data' => $data,
                ]);
            }

            if ($this->throwOnError) {
                throw $exception;
            }

            return null;
        }

        if ($response->failed() && $this->logsEnabled) {
            Log::warning("Rybbit $path request failed: ".$response->status(), [
                'data' => $data,
                'response' => $response->body(),
            ]);
        }

        $response->throwIf($this->throwOnError);

        return $response;
    }
}
