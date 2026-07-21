<?php

namespace App\Console\Commands;

use App\Services\ShopifyCatalogService;
use App\Services\TracezillaSkuService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Throwable;

class TracezillaSkusFromShopifyCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tracezilla:skus-from-shopify';

    /**
     * The console command description.
     */
    protected $description = 'Create Tracezilla test SKUs from Shopify product variants';

    /**
     * Execute the console command.
     */
    public function handle(
        ShopifyCatalogService $shopifyCatalog,
        TracezillaSkuService $tracezillaSkus,
    ): int {
        $this->info('Fetching Shopify product variants...');

        $variants = $shopifyCatalog->getProductVariants();

        $this->info(sprintf(
            '%d Shopify product variant(s) returned.',
            count($variants)
        ));

        $this->info('Fetching existing Tracezilla SKUs...');

        $existingSkuCodes = $tracezillaSkus->getSkuCodes();

        $this->info(sprintf(
            '%d Tracezilla SKU(s) found.',
            count($existingSkuCodes)
        ));

        $created = [];
        $skipped = [];
        $failed = [];

        foreach ($variants as $variant) {
            if (empty($variant['sku'])) {
                continue;
            }

            if (in_array($variant['sku'], $existingSkuCodes, true)) {
                $skipped[] = $variant['sku'];
                continue;
            }

            try {
                $created[] = [
                    'sku' => $variant['sku'],
                    'response' => $tracezillaSkus->createTestSku([
                        'sku_code' => $variant['sku'],
                        'global_name' => $variant['sku'],
                        'weight_factor_net' => 1,
                        'weight_factor_gross' => 1,
                        'unit_of_measure' => 'pcs',
                        'lot_unit' => 'colli',
                        'default_uom_conversion' => 1,
                        'weight_factor_net' => 1,
                        'weight_factor_gross' => 1,
                    ]),
                ];
            } catch (RequestException $e) {
                $failed[] = [
                    'sku' => $variant['sku'],
                    'status' => $e->response->status(),
                    'error' => $e->response->json(),
                ];
            } catch (Throwable $e) {
                $failed[] = [
                    'sku' => $variant['sku'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        $result = [
            'created_count' => count($created),
            'skipped_count' => count($skipped),
            'failed_count' => count($failed),
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
        ];

        $this->info(sprintf(
            'Created: %d, skipped: %d, failed: %d',
            $result['created_count'],
            $result['skipped_count'],
            $result['failed_count']
        ));

        $this->newLine();
        $this->line(json_encode($result, JSON_PRETTY_PRINT));

        return $result['failed_count'] > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}