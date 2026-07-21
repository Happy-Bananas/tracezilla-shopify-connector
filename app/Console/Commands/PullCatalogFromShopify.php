<?php

namespace App\Console\Commands;

use App\Clients\ShopifyClient;
use App\Clients\TracezillaClient;
use App\GraphQL\Queries\GetProductVariants;
use Illuminate\Console\Command;

class PullCatalogFromShopify extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pull-catalog-from-shopify';

    /**
     * The console command description.
     */
    protected $description = 'Pull catalog and prices from Shopify to Tracezilla';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to pull catalog from Shopify...');

        $client = new ShopifyClient();

        $productVariants = [];
        $after = null;

        do {
            $result = $client->graphql(
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

        $this->info(sprintf(
            '%d product variant(s) returned.',
            count($productVariants)
        ));

        $shopifySkuMapping = [];

        foreach ($productVariants as $variant) {
            if (empty($variant['sku'])) {
                continue;
            }

            $shopifySkuMapping[$variant['sku']] = [
                'variant_id' => $variant['legacyResourceId'],
                'price' => $variant['price'],
            ];
        }

        $this->newLine();

        $this->info('Fetching SKUs from Tracezilla...');

        $tracezilla = new TracezillaClient();

        $skus = $tracezilla->http()
            ->get('/skus', [
                'sortBy' => 'sku_code',
                'sortDirection' => 'asc',
            ])
            ->throw()
            ->json();

        $this->line(json_encode($skus, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}