<?php

namespace App\Services;

use App\Clients\TracezillaClient;

class TracezillaSkuService
{
    public function __construct(
        protected TracezillaClient $client,
    ) {
    }

    public function getSkuCodes(): array
    {
        $response = $this->client
            ->http()
            ->get('/skus', [
                'sortBy' => 'sku_code',
                'sortDirection' => 'asc',
                'perPage' => 250,
            ])
            ->throw()
            ->json();

        return collect($response['data'])
            ->pluck('sku_code')
            ->filter()
            ->values()
            ->all();
    }

    public function createTestSku(array $payload): array
    {
        return $this->client
            ->http()
            ->post('/skus', $payload)
            ->throw()
            ->json();
    }

    public function listSkus(int $limit = 10): array
    {
        $response = $this->client
            ->http()
            ->get('/skus', [
                'sortBy' => 'sku_code',
                'sortDirection' => 'asc',
                'perPage' => $limit,
            ])
            ->throw()
            ->json();

        return $response['data'];
    }
}