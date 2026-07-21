<?php

namespace App\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ShopifyClient
{
    /**
     * Shopify shop URL.
     */
    protected string $shopUrl;

    /**
     * Shopify client ID.
     */
    protected string $clientId;

    /**
     * Shopify client secret.
     */
    protected string $clientSecret;

    /**
     * Shopify warehouse ID.
     */
    protected ?string $warehouseId;

    /**
     * Authenticated HTTP client.
     */
    protected PendingRequest $http;

    public function __construct()
    {
        $config = config('services.shopify');

        $this->shopUrl = $config['shop_url'];
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->warehouseId = $config['warehouse_id'] ?? null;

        $response = Http::asForm()->post(
            "https://{$this->shopUrl}/admin/oauth/access_token",
            [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope'         => $config['scope'],
            ]
        );

        $payload = $response->throw()->json();

        $this->http = Http::baseUrl(
                "https://{$this->shopUrl}/admin/api/2025-10"
            )
            ->acceptJson()
            ->withHeaders([
                'X-Shopify-Access-Token' => $payload['access_token'],
            ]);
    }

    /**
     * Get authenticated HTTP client.
     */
    public function http(): PendingRequest
    {
        return $this->http;
    }

    public function graphql(string $query, array $variables = []): array
    {
        $payload = [
            'query' => $query,
        ];

        if (!empty($variables)) {
            $payload['variables'] = $variables;
        }

        return $this->http
            ->post('/graphql.json', $payload)
            ->throw()
            ->json();
    }
}

// $client = new App\Clients\ShopifyClient();
// $response = $client->http()->get('/shop.json');