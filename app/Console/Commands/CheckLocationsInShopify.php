<?php

namespace App\Console\Commands;

use App\Clients\ShopifyClient;
use App\GraphQL\Queries\GetLocations;
use Illuminate\Console\Command;

class CheckLocationsInShopify extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shopify:locations';

    /**
     * The console command description.
     */
    protected $description = 'List locations from Shopify';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Fetching locations from Shopify...');

        $client = new ShopifyClient();

        $result = $client->graphql(
            GetLocations::QUERY,
            [
                'first' => 10,
            ]
        );

        $locations = $result['data']['locations']['nodes'];

        $this->info(sprintf(
            '%d location(s) returned.',
            count($locations)
        ));

        $this->newLine();

        $this->line(json_encode($locations, JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}