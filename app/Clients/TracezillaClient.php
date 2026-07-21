<?php

namespace App\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class TracezillaClient
{
    /**
     * Tracezilla base URL.
     */
    protected string $baseUrl;

    /**
     * Tracezilla team slug.
     */
    protected string $teamSlug;

    /**
     * Tracezilla API key.
     */
    protected string $apiKey;

    /**
     * Authenticated HTTP client.
     */
    protected PendingRequest $http;

    public function __construct()
    {
        $config = config('services.tracezilla');

        $this->baseUrl = $config['base_url'];
        $this->teamSlug = $config['team_slug'];
        $this->apiKey = $config['api_key'];

        $this->http = Http::baseUrl(
                "{$this->baseUrl}/api/v1/{$this->teamSlug}"
            )
            ->acceptJson()
            ->withToken($this->apiKey);
    }

    /**
     * Get authenticated HTTP client.
     */
    public function http(): PendingRequest
    {
        return $this->http;
    }
}