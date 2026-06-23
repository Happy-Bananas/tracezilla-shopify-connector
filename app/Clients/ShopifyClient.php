<?php

namespace App\Clients;

use Illuminate\Support\Facades\Http;
use PHPShopify\ShopifySDK;

class ShopifyClient
{
    /**
     * Shopify shop url.
     */
    protected string $shopUrl;

    /**
     * Shopify client id.
     */
    protected string $clientId;

    /**
     * Shopify client secret.
     */
    protected string $clientSecret;

    /**
     * Shopify warehouse id.
     */
    protected ?string $warehouseId;

    /**
     * Shopify SDK connection.
     */
    protected ShopifySDK $connection;

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
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => $config['scope'],
            ]
        );

        $payload = $response->throw()->json();

        $this->connection = new ShopifySDK([
            'ShopUrl' => $this->shopUrl,
            'AccessToken' => $payload['access_token'],
        ]);
    }

    public function connection(): ShopifySDK
    {
        return $this->connection;
    }
}