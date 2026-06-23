<?php

namespace App\Connectors;

use PHPShopify\ShopifySDK;
use Illuminate\Support\Facades\Http;


class ShopifyConnection {
    /**
     * Shopify shop url
     */
    protected $shopUrl;

    /**
     * Shopify api key
     */
    protected $apiKey;

    /**
     * Warehouse id
     */
    protected $warehouseId;

    /**
     * ShopifySdk connection
     */
    protected $connection = null;

    /**
     * Name of tag to look for when searching skus
     */
    protected $skuTagName;

    public function __construct()
    {
        $this->shopUrl                  = env('SHOPIFY_SHOP_URL');
        $this->apiKey                   = env('SHOPIFY_API_KEY');
        $this->warehouseId              = env('SHOPIFY_WAREHOUSE_ID');

        $response = Http::asForm()->post('https://' .env('SHOPIFY_SHOP_URL') . '/admin/oauth/access_token', [
            'grant_type' => 'client_credentials',
            'client_id' => env('SHOPIFY_CLIENT_ID'),
            'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
            'scope' => 'read_locations',
        ]);

        $payload = $response->json();

        $bearerToken = $payload['access_token'];

        $this->connection = new ShopifySDK([
            'ShopUrl' => $this->shopUrl,
            'AccessToken' => $bearerToken
        ]);

    }

    /**
     * Get connection
     */
    public function connection()
    {
        return $this->connection;
    }
}