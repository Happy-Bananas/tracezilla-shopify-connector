<?php

namespace App\Services;

use App\Clients\ShopifyClient;
use App\GraphQL\Queries\GetProductVariants;

class ShopifyCatalogService
{
    public function __construct(
        protected ShopifyClient $client,
    ) {
    }

    public function getProductVariants(): array
    {
        $productVariants = [];
        $after = null;

        do {
            $result = $this->client->graphql(
                GetProductVariants::QUERY,
                [
                    'first' => 250,
                    'after' => $after,
                ]
            );

            $connection = $result['data']['productVariants'];

            $productVariants = array_merge(
                $productVariants,
                $connection['nodes']
            );

            $after = $connection['pageInfo']['endCursor'];
        } while ($connection['pageInfo']['hasNextPage']);

        return $productVariants;
    }

    public function getVariantSkuMapping(): array
    {
        $mapping = [];

        foreach ($this->getProductVariants() as $variant) {
            if (empty($variant['sku'])) {
                continue;
            }

            $mapping[$variant['sku']] = [
                'variant_id' => $variant['legacyResourceId'],
                'price' => $variant['price'],
            ];
        }

        return $mapping;
    }
}